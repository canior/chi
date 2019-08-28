<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/10
 * Time: 19:23
 */
namespace App\Controller\AppApi;


use App\Entity\CourseOrder;
use App\Entity\CourseStudent;
use App\Entity\GroupUserOrder;
use App\Repository\CategoryRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserAddressRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

/**
 * Class GroupUserOrderController
 * @Route("/auth")
 * @package App\Controller\AppApi
 * @author zxqc2018
 */
class GroupUserOrderController extends AppApiBaseController
{
    /**
     * 订单待支付页面
     *
     * @Route("/groupUserOrder/view", name="appViewGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function viewAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'url', 'groupUserOrderId',
        ], ['groupUserOrderId']);
        $groupUserOrderId = $requestProcess['groupUserOrderId'];
        $url = $requestProcess['url'];
        /**
         * @var GroupUserOrder $groupUserOrder
         */
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);


        $user = $this->getAppUser();

        if (empty($groupUserOrder) || $user !== $groupUserOrder->getUser()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS, []);
        }

        $courseStudentArray = [];
        if ($groupUserOrder->getProduct()->isCourseProduct()) {
            /**
             * @var CourseStudent[] $courseStudents
             */
            $courseStudents = $this->getEntityManager()->getRepository(CourseStudent::class)->findBy(["course" => $groupUserOrder->getProduct()->getCourse(), "studentUser" => $user]);
            foreach ($courseStudents as $courseStudent) {
                $courseStudentArray[] = $courseStudent->getArray();
            }
        }

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray(),
            'courseStudents' => $courseStudentArray,
            'shareSources' => [],
        ];

        return CommonUtil::resultData()->toJsonResponse($data);
    }

    /**
     * 选择订单地址
     * @Route("/groupUserOrder/confirmAddress", name="appGroupUserOrderConfirmAddress", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function confirmAddressAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, UserAddressRepository $userAddressRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'addressId', 'groupUserOrderId',
        ], ['groupUserOrderId', 'addressId']);


        $addressId = $requestProcess['addressId'];
        $groupUserOrderId = $requestProcess['groupUserOrderId'];

        $user = $this->getAppUser();
        $userAddress = $userAddressRepository->find($addressId);

        if (empty($userAddress) || $userAddress->getUser() !== $user) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_ADDRESS_NOT_EXISTS, []);
        }

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);
        if (empty($groupUserOrder) || $user !== $groupUserOrder->getUser()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS, []);
        }

        //每次订单的地址自动成为用户默认地址
        $user->setDefaultAddress($userAddress);
        $this->entityPersist($user, false);

        $groupUserOrder->setUserAddress($userAddress);
        $this->entityPersist($groupUserOrder);

        return CommonUtil::resultData()->toJsonResponse([
            'groupUserOrder' => $groupUserOrder->getArray()
        ]);

    }

    /**
     * 金秋的创建订单，需要创建金秋的升级订单
     * 普通购买产品创建用户订单
     * @Route("/groupUserOrder/create", name="appCreateGroupUserOrder", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'productId', 'unlockCategoryId'
        ], ['productId', 'unlockCategoryId']);

        $productId = $requestProcess['productId'];
        $unlockCategoryId = $requestProcess['unlockCategoryId'];
        $user = $this->getAppUser();
        $product = $productRepository->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        $groupUserOrder = GroupUserOrder::factory($user, $product);

        $userDefaultAddress = $user->getDefaultUserAddress();

        if (!empty($userDefaultAddress)) {
            $groupUserOrder->setUserAddress($userDefaultAddress);
        }

        //解锁系列课
        if (!empty($unlockCategoryId)) {
            $unlockCategory = $categoryRepository->find($unlockCategoryId);
            if (empty($unlockCategory->getParentCategory()) || $unlockCategory->isSingleCourse()) {
                $requestProcess->throwErrorException(ErrorCode::ERROR_UNLOCK_CATEGORY_NOT_PRIVILEGE, []);
            }
            $groupUserOrder->setUnlockCategory($unlockCategory);
        }
        $this->entityPersist($groupUserOrder);

        return $requestProcess->toJsonResponse([
            'groupUserOrder' => $groupUserOrder->getArray()
        ]);
    }

    /**
     * 普通购买活动订单
     * @Route("/groupUserOrder/createOfflineCourse", name="appCreateOfflineCourseGroupUserOrder", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createOfflineCourseAction(Request $request, ProductRepository $productRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'productId'
        ], ['productId']);

        $productId = $requestProcess['productId'];
        $user = $this->getAppUser();
        $product = $productRepository->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        $offlineCourseOrder = CourseOrder::factory($user, $product);

        $this->entityPersist($offlineCourseOrder);

        return $requestProcess->toJsonResponse([
            'groupUserOrder' => $offlineCourseOrder->getArray()
        ]);
    }

    /**
     * 我的订单列表
     *
     * 全部，待成团， 待发货， 已发货， 待收货
     *
     * 全部: status = null, paymentStatus in ['paid', 'refunding', 'refunded']
     * 待成团: status = 'created', paymentStatus = 'paid'
     * 待发货: status = 'pending', paymentStatus = 'paid'
     * 已发货：status = 'shipping' paymentStatus = 'paid'
     * 已收货: status = 'delivered' paymentStatus = 'paid'
     *
     * @Route("/groupUserOrders", name="appApiGroupUserOrders", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function appGroupUserOrdersAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, UserRepository $userRepository) {

        $data = $this->processRequest($request, ['productType', 'page','recommander'], ['productType']);

        $groupUserOrderStatus = isset($data['groupUserOrderStatus']) ? $data['groupUserOrderStatus'] : null;
        $page = isset($data['page']) ? $data['page'] : 1;

        /**
         * product, onlineCourse, offlineCourse
         */
        $productType = isset($data['productType']) ? $data['productType'] : '';
        $productCategory = isset($data['productCategory']) ? $data['productCategory'] :'';
        $recommander = isset($data['recommander']) ? $data['recommander'] :false;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        if ($groupUserOrderStatus == null){
            $groupUserOrderStatus =  array_keys(GroupUserOrder::$statuses);
        }
        $paymentStatusArray = ['paid', 'refunding', 'refunded'];

        switch ($productType) {
            case 'product':
                $where = [
                    'status' => $groupUserOrderStatus,
                    'paymentStatus' => $paymentStatusArray,
                    'isCourseProduct'=>false,
                ];
                break;
            case 'onlineCourse':
                $where = [
                    'status' => $groupUserOrderStatus,
                    'paymentStatus' => $paymentStatusArray,
                    'isCourseProduct'=>true,
                    'isOnline'=>true
                ];
                break;
            case 'offlineCourse':
                $where = [
                    'status' => $groupUserOrderStatus,
                    'paymentStatus' => $paymentStatusArray,
                    'isCourseProduct'=>true,
                    'isOnline'=>false
                ];
                break;
            default:
                break;
        }

        // 推荐人订单，自己订单
        if($recommander){
            $recommandersUser = $userRepository->getUserByParent($user->getId());
            $recommanders = [];
            foreach ($recommandersUser as $k => $v) {
                $recommanders[] = $v->getId();
            }
            $where['recommanders'] = $recommanders;
        }else{
            $where['userId'] = $user->getId();
        }

        $groupUserOrders = $groupUserOrderRepository->findUserGroupUserOrders($where);
        $groupUserOrders = $this->getPaginator()->paginate($groupUserOrders, $page,self::PAGE_LIMIT);
        $groupUserOrdersCount = $groupUserOrderRepository->findUserGroupUserOrders($where,true);

        $groupUserOrdersArray = [];
        foreach ($groupUserOrders as $groupUserOrder) {
            $groupUserOrdersArray[] = $groupUserOrder->getArray();
        }

        // 返回
        return CommonUtil::resultData( [
            'groupUserOrders' => $groupUserOrdersArray,
            'total' => CommonUtil::getTotalQueryCount($groupUserOrdersCount),
        ] )->toJsonResponse();
    }
}