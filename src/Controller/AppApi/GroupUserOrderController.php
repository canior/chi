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
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserAddressRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $data = json_decode($request->getContent(), true);
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;
        $url = isset($data['url']) ? $data['url'] : null;

        /**
         * @var GroupUserOrder $groupUserOrder
         */
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);
        $user = $groupUserOrder->getUser();

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
            'shareSources' => $this->createProductShareSource($user, $groupUserOrder->getProduct(), $url)
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
        $data = json_decode($request->getContent(), true);
        $addressId = isset($data['addressId']) ? $data['addressId'] : null;
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;

        $user = $this->getAppUser();
        $userAddress = $userAddressRepository->find($addressId);

        //每次订单的地址自动成为用户默认地址
        $user->setDefaultAddress($userAddress);
        $this->getEntityManager()->persist($user);

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);
        $groupUserOrder->setUserAddress($userAddress);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request, ProductRepository $productRepository)
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

        $groupUserOrder = GroupUserOrder::factory($user, $product);

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
}