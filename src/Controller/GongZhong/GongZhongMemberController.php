<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/23
 * Time: 20:26
 */

namespace App\Controller\GongZhong;

use App\Entity\BianxianUserLevel;
use App\Entity\CourseStudent;
use App\Entity\ProductReview;
use App\Entity\ProductReviewImage;
use App\Repository\FileRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Service\Order\OfflineTableNo;
use App\Service\Product\OfflineCourseService;
use App\Service\Util\FactoryUtil;
use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use App\Service\Util\CommonUtil;
use App\Service\ErrorCode;
use App\Entity\MessageCode;
use App\Repository\MessageCodeRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\UserAddressRepository;
use App\Repository\RegionRepository;
use App\Entity\UserAddress;
use App\Entity\Region;
use App\Entity\UserAccountOrder;
use App\Repository\GroupUserOrderRepository;
use App\Entity\ProjectBannerMeta;
use App\Repository\GroupOrderRepository;
use App\Repository\FollowRepository;
use App\Entity\Follow;
use App\Repository\CourseRepository;
use App\Repository\TeacherRepository;
use App\Entity\GroupUserOrder;
use App\Entity\Message;
use App\Entity\Product;
use App\Repository\MessageRepository;
use App\Repository\UserAccountOrderRepository;
use App\Service\Config\ConfigParams;
use App\Service\Document\WeChatDocument;
use App\Repository\FollowCourseMetaRepository;
use App\Repository\FollowTeacherMetaRepository;
use App\Entity\FollowCourseMeta;
use App\Entity\FollowTeacherMeta;
use App\Repository\MessageGroupUserOrderMetaRepository;
use App\Repository\UserRecommandStockOrderRepository;
use App\Service\Util\MoneyUtil;

/**
 */
class GongZhongMemberController extends GongZhongBaseController
{
    


    /**
     * 我的关注列表
     * @Route("/gzhAuth/follow", name="gzhFollow", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function followAction(Request $request, FollowCourseMetaRepository $followCourseMetaRepository, FollowTeacherMetaRepository $followTeacherMetaRepository) {

        $data = $this->processRequest($request, ['type', 'page'], ['type']);
        $type = isset($data['type']) ? $data['type'] : '';
        $page = isset($data['page']) ? $data['page'] : 1;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 查询
        switch ($type) {
            case 'onlineCourse':
            case 'offlineCourse':
                $followArray = $followCourseMetaRepository->findMyFollow($user->getId(),$type,$page,self::PAGE_LIMIT);
                break;
            case 'Teacher':
                $followArray = $followTeacherMetaRepository->findMyFollow($user->getId(),$page,self::PAGE_LIMIT);
                break;
            default:
                break;
        }
        
        $follows = [];
        foreach ($followArray as $k => $v) {
            $follows[] = ['id'=>$v['id'],$type=>$v[0]->getArray()];
        }
        
        // 返回
        return CommonUtil::resultData(  ['follow'=>$follows] )->toJsonResponse();
    }


    /**
     * 我的名额统计
     * @Route("/gzhAuth/children", name="children", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function childrenAction(Request $request) {
        
        $data = $this->processRequest($request);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData( [
            'user'=>$user->getArray(),
            'totalStock' => $user->getTotalRecommandStock(),//拥有的总名额
            'usedStock' => $user->getUsedRecommandStockCount(),//用掉的总名额
            'recommandStock' => $user->getRecommandStock(),//剩余名额
        ] )->toJsonResponse();
    }

    /**
     * 我的系统学员已用名额列表
     * @Route("/gzhAuth/childrenUse", name="childrenUse", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function childrenUseAction(Request $request, UserRecommandStockOrderRepository $userRecommandStockOrderRepository) {
        
        $data = $this->processRequest($request,['page']);

        $page = isset($data['page']) ? $data['page'] : 1;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 获取用户
        $userStockOrders = $userRecommandStockOrderRepository->getUserRecommandStockOrders($user->getId());
        $userStockOrders = $this->getPaginator()->paginate($userStockOrders, $page,self::PAGE_LIMIT);

        $childrenArray = [];
        foreach ($userStockOrders as $userStockOrder) {
            $childrenArray[] = $userStockOrder->getArray();
        }

        // 返回
        return CommonUtil::resultData( ['children' => $childrenArray] )->toJsonResponse();
    }


    /**
     * 我的消息列表
     * @Route("/gzhAuth/message", name="message", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function messageAction(Request $request, MessageGroupUserOrderMetaRepository $messageGroupUserOrderMetaRepository) {

        $data = $this->processRequest($request,['page','checkStatus','isRead','isNewUser']);

        $page = isset($data['page']) ? $data['page'] : 1;
        $checkStatus = isset($data['checkStatus']) ? $data['checkStatus'] : '';
        $isRead = isset($data['isRead']) ? $data['isRead'] : '';
        $isNewUser = isset($data['isNewUser']) ? $data['isNewUser'] : false;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $messageQuery = $messageGroupUserOrderMetaRepository->getGroupUserOrder($user->getId(),$checkStatus,$isRead,$isNewUser);
        $messageArrays = $this->getPaginator()->paginate($messageQuery, $page, self::PAGE_LIMIT);

        $orderArray = [];
        $idsArray = [];
        foreach ($messageArrays as $order) {
            if( isset($order[0]) ){
                $order['groupUserOrder'] = $order[0]->getArray();
                unset($order[0]);
                $orderArray[] = $order;
                $idsArray[] = $order['id'];
            }
        }

        // 查询列表 标记已读
        // $messageGroupUserOrderMetaRepository->setMessagesIsRead($idsArray);

        // 获取返还金产品 返还金专用产品
        // TODO

        // 返回
        return CommonUtil::resultData(  ['messageArray'=>$orderArray,'productId'=116 ] )->toJsonResponse();
    }

    /**
     * 修改审核状态
     * @Route("/gzhAuth/updateOrder", name="updateOrder", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function updateOrderAction(Request $request,GroupUserOrderRepository $groupUserOrderRepository) {

        $data = $this->processRequest($request,['groupOrdersId','checkStatus','reason','carrierName','trackingNo','setDelivered'],['groupOrdersId']);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $groupOrdersId = isset($data['groupOrdersId']) ? $data['groupOrdersId'] : null;
        $groupOrder = $groupUserOrderRepository->find( $groupOrdersId );
        
        //审核
        $checkStatus = isset($data['checkStatus']) ? $data['checkStatus'] : null;
        $reason = isset($data['reason']) ? $data['reason'] : null;
        if($checkStatus){
            $groupOrder->setCheckStatus($checkStatus);
        }
        if($reason){
            $groupOrder->setReason($reason);
        }

        // 发货
        $carrierName = isset($data['carrierName']) ? $data['carrierName'] : null;
        $trackingNo = isset($data['trackingNo']) ? $data['trackingNo'] : null;
        if($carrierName && $trackingNo){
            $groupOrder->setCarrierName($carrierName);
            $groupOrder->setTrackingNo($trackingNo);
            $groupOrder->setShipping();
        }

        //  确认收货
        $setDelivered = isset($data['setDelivered']) ? $data['setDelivered'] : null;
        if($setDelivered){
            $groupOrder->setDelivered();
        }
        
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['groupOrder' => $groupOrder->getArray()] )->toJsonResponse();
    }


    /**
     * 已读 消息 
     * @Route("/gzhAuth/postMessage", name="postMessage", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function postMessageAction(Request $request, MessageRepository $messageRepository) {

        $data = $this->processRequest($request,['id','isRead'],['id']);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $id = isset($data['id']) ? $data['id'] : null;
        $isRead = isset($data['isRead']) ? $data['isRead'] : null;

        // 是否已经存在
        $message = $messageRepository->find($id);
        if( !$message ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_MESSAGE_NOT_FIND )->toJsonResponse();
        }

        // 持久化
        $message->setIsRead($isRead);
        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['message_id' => $message->getId() ] )->toJsonResponse();
    }

    /**
     * 删除 消息 
     * @Route("/gzhAuth/delMessage", name="delMessage", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function delMessageAction(Request $request, MessageRepository $messageRepository) {

        $data = $this->processRequest($request,['id'],['id']);
        $id = isset($data['id']) ? $data['id'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $message = $messageRepository->find($id);
        if( !$message ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_MESSAGE_NOT_FIND )->toJsonResponse();
        }

        $this->getEntityManager()->remove($message);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['message' => $message ] )->toJsonResponse();
    }

    /**
     * @Route("/gzhAuth/userInfo", name="apiUserInfo",  methods={"POST"})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userInfo(Request $request)
    {
        $data = $this->processRequest($request);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData( ['user'=>$user->getArray()] )->toJsonResponse();
    }

    /**
     * 我发起的思维课
     *
     * @Route("/gzhAuth/initiatorOfflineCourses", name="gzhinitiatorOfflineCourseIndex", methods={"POST","OPTIONS"})
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function initiatorOfflineCoursesAction(Request $request, ProductRepository $productRepository) : JsonResponse
    {
        $requestProcess = $this->processRequest($request, ['offlineCourseType','isEnd', 'page', 'pageNum'], ['offlineCourseType']);
        $user = $this->getAppUser();

        $courseQuery = $productRepository->findAppProductsQueryBuilder(true, false, [
            'offlineCourseType' => $requestProcess['offlineCourseType'],
            'isEnd' => $requestProcess['isEnd'],
            'initiator'=>$user
        ]);
        $courseList = $this->getPaginator()->paginate($courseQuery, $requestProcess['page'], $requestProcess['pageNum']);

        $courseCountQuery = $productRepository->findAppProductsQueryBuilder(true, false, [
            'offlineCourseType' => $requestProcess['offlineCourseType'],
            'isEnd' => $requestProcess['isEnd'],
            'initiator'=>$user,
            'isGetCount' => true
        ]);

        $data  = [];
        foreach ($courseList as $k => $v) {
            $item = $v->getArray();
            $item['is_initiator'] = false;
            if( $v->getCourse()->getInitiator() && $v->getCourse()->getInitiator()->getId() ==  $user->getID() ){
                $item['is_initiator'] = true;
            }
            $data[] = $item;
        }

        return $requestProcess->toJsonResponse([
            'courseList' => $data,
            'total' => CommonUtil::getTotalQueryCount($courseCountQuery),
        ]);
    }
}