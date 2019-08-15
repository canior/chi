<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/10
 * Time: 19:23
 */
namespace App\Controller\GongZhong;


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
use App\Service\Util\FactoryUtil;
use App\Service\Util\MoneyUtil;
use App\Repository\UserRepository;

/**
 */
class GongZhongGroupUserOrderController extends GongZhongBaseController
{
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
     * @Route("/gzhAuth/groupUserOrders", name="gzhGroupUserOrders", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function groupUserOrdersAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, UserRepository $userRepository) {

        $data = $this->processRequest($request, ['productType', 'page','recommander'], ['productType']);

        $groupUserOrderStatus = isset($data['groupUserOrderStatus']) ? $data['groupUserOrderStatus'] : null;
        $page = isset($data['page']) ? $data['page'] : 1;

        /**
         * product, onlineCourse, offlineCourse
         */
        $productType = isset($data['productType']) ? $data['productType'] : '';
        $productCategory = isset($data['productCategory']) ? $data['productCategory'] :'';
        $recommander = isset($data['recommander']) ? $data['recommander'] :'';

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