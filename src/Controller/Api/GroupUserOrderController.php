<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-30
 * Time: 2:32 AM
 */

namespace App\Controller\Api;


use App\Command\EnqueueCommand;
use App\Command\Notification\NotifyCompletedGroupOrderCommand;
use App\Command\Notification\NotifyPendingGroupOrderCommand;
use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\UserRepository;
use App\Service\Wx\WxCommon;
use App\Service\Wx\WxPayment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class GroupUserOrderController extends BaseController
{
    /**
     * 选择订单地址
     *
     * @Route("/groupUserOrder/chooseAddress", name="groupOrderChooseAddress", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function chooseAddressAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) {
        $data = json_decode($request->getContent(), true);

        $groupOrderId =  isset($data['groupOrderId']) ? $data['groupOrderId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
    }

    /**
     * 支付订单 (开团，参团）
     *
     * @Route("/groupUserOrder/pay", name="payGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function payAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) {
        $data = json_decode($request->getContent(), true);

        $groupUserOrderId =  isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        $body = "create order"; //TODO 开团信息要怎么写
        $wxPaymentApi = new WxPayment($this->getLog());
        $result = $wxPaymentApi->getPrepayId($user->getWxOpenId(), $groupUserOrder->getId(), $groupUserOrder->getTotal(), $body);
        $prePayId = $result['prepay_id'];
        $prePayInfo = $wxPaymentApi->getOrderDataToWxApp($prePayId);

        $groupUserOrder->setPrePayId($prePayId);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray(),
            'payment' => $prePayInfo,
        ];

        return $this->responseJson('success', 200, $data);
    }

    /**
     * 支付开团订单成功
     * 1. 更新拼团订单状态pending（拼团中）
     * 2. 微信支付通知
     *
     * 团员支付参团订单成功
     * 1. 微信支付通知
     * 2. 拼团的状态改为completed（拼团成功）
     * 3. 团长小程序通知拼团成功
     *
     * 支付普通订单成功
     *
     * 返回拼团详情页
     *
     * @Route("/groupUserOrder/notifyPayment", name="notifyGroupUserOrderPayment", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function notifyPaymentAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, UserRepository $userRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $isPaid =  isset($data['isPaid']) ? $data['isPaid'] : false;
        $groupUserOrderId =  isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        if (!$isPaid) {
            return $this->responseJson('group_order_created_fail', 200, $data);
        }

        if ($groupUserOrder->isGroupOrder()) {
            $groupOrder = $groupUserOrder->getGroupOrder();
            if ($groupUserOrder->isMasterOrder()) {
                $groupOrder->setPending();
                //$command = new NotifyPendingGroupOrderCommand($groupOrder->getId());
            } else {
                $groupOrder->setCompleted($user);
                //$command = new NotifyPendingGroupOrderCommand($groupOrder->getId());
            }
            $this->getEntityManager()->persist($groupOrder);
            $this->getEntityManager()->flush();
        } else {
            $groupUserOrder->setPending();
            $groupUserOrder->setPaid();
            $this->getEntityManager()->persist($groupUserOrder);
            $this->getEntityManager()->flush();
            //$command = new NotifyPendingGroupOrderCommand($groupOrder->getId());
        }

        //$this->getCommandBus()->handle($command);

//        if ($groupOrder->isPending()) {
//
//            $groupUserOrder = $groupOrder->getMasterGroupUserOrder();
//
//            $formId = $groupUserOrder->getPrePayId();
//            $templateId = "9RLVQOC7gP3qJipiFX2efKvz2oSYuJRs0dDaY2UeDIA";
//            $page = "pages/group/index?id=" . $groupOrderId;
//            $toUser = $groupUserOrder->getUser()->getWxOpenId();
//            $data = ['keyword1' => ['value' => $groupOrder->getProduct()->getTitle()],
//                'keyword2' => ['value' => $groupOrder->getProduct()->getPrice()],
//                'keyword3' => ['value' => 1],
//                'keyword4' => ['value' => '2019-01-01 12:12:12']];
//            $emphasisKeyword = "keyword3.DATA";
//
//            $wxApi = new WxCommon($this->getLog());
//            $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
//
//            //$command = new NotifyPendingGroupOrderCommand($groupOrder->getId());
//            //$this->getCommandBus()->handle($command);
//        } else if ($groupOrder->isCompleted()) {
//            $command = new NotifyCompletedGroupOrderCommand($groupOrder->getId());
//            //$this->getCommandBus()->handle($command);
//        }

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray()
        ];
        return $this->responseJson('success', 200, $data);

    }
}