<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 5:54 PM
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
class GroupOrderController extends BaseController
{
    /**
     * 测试
     * @Route("/groupOrder/test", name="testGroupOrder", methods="POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request) {
        if ($this->getEnvironment() != 'dev') exit;

        $data = json_decode($request->getContent(), true);
        $productId =  isset($data['productId']) ? $data['productId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $product = $this->getEntityManager()->getRepository(Product::class)->find($productId);

        //开团
        $groupOrder = new GroupOrder($user, $product);
        $groupOrder->setPending();

        //参团
        $joiner = $this->getEntityManager()->getRepository(User::class)->find(2);
        $joinerGroupUserOrder = new GroupUserOrder($groupOrder, $joiner);
        $groupOrder->addGroupUserOrder($joinerGroupUserOrder);

        //完成拼团
        $groupOrder->setCompleted($joiner);

        //团长确认收货
        $groupOrder->getMasterGroupUserOrder()->setDelivered();


        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        $data = [
            'groupOrder' => $groupOrder->getArray()
        ];

        return $this->responseJson('success', 200, $data);
    }

    /**
     * 创建开团订单
     *
     * @Route("/groupOrder/create", name="createGroupOrder", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request) {

        $data = json_decode($request->getContent(), true);

        $productId =  isset($data['productId']) ? $data['productId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);

        /**
         * @var Product $product
         */
        $product = $this->getEntityManager()->getRepository(Product::class)->find($productId);

        $groupOrder = new GroupOrder($user, $product);
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        //向微信提交支付信息
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $body = "create order"; //TODO 开团信息要怎么写
        $wxPaymentApi = new WxPayment($this->getLog());
        $result = $wxPaymentApi->getPrepayId($user->getWxOpenId(), $groupUserOrder->getId(), $groupUserOrder->getTotal(), $body);
        $prePayId = $result['prepay_id'];
        $prePayInfo = $wxPaymentApi->getOrderDataToWxApp($prePayId);

        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();
        $groupUserOrder->setPrePayId($prePayId);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();


        $data = [
            'payment' => $prePayInfo,
            'groupOrder' => $groupOrder->getArray()
        ];

        return $this->responseJson('success', 200, $data);
    }

    /**
     * 创建参团订单
     *
     * @Route("/groupOrder/join", name="joinGroupOrder", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function joinAction(Request $request, GroupOrderRepository $groupOrderRepository) {

        $data = json_decode($request->getContent(), true);

        $groupOrderId =  isset($data['groupOrderId']) ? $data['groupOrderId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);

        $groupOrder = $groupOrderRepository->find($groupOrderId);


        $groupUserOrder = $groupOrder->getSlaveGroupUserOrder($user);
        if ($groupUserOrder == null) {
            $groupUserOrder = new GroupUserOrder($groupOrder, $user);
            $groupOrder->addGroupUserOrder($groupUserOrder);
        }
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        //微信提交支付
        $body = "create order"; //TODO 参团信息要怎么写
        $wxPaymentApi = new WxPayment($this->getLog());
        $this->getLog()->info("join group user order: id=" . $groupUserOrder->getId() .  ' total=' . $groupUserOrder->getTotal());
        $result = $wxPaymentApi->getPrepayId($user->getWxOpenId(), $groupUserOrder->getId(), $groupUserOrder->getTotal(), $body);
        $prePayId = $result['prepay_id'];
        $prePayInfo = $wxPaymentApi->getOrderDataToWxApp($prePayId);

        $groupUserOrder->setPrePayId($prePayId);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();


        $data = [
            'payment' => $prePayInfo,
            'groupOrder' => $groupOrder->getArray()
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
     * 返回拼团详情页
     *
     * @Route("/groupOrder/notifyPayment", name="notifyGroupOrderPayment", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function notifyPaymentAction(Request $request, GroupOrderRepository $groupOrderRepository, UserRepository $userRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $isPaid =  isset($data['isPaid']) ? $data['isPaid'] : false;
        $groupOrderId =  isset($data['groupOrderId']) ? $data['groupOrderId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $groupOrder = $groupOrderRepository->find($groupOrderId);

        if (!$isPaid) {
            return $this->responseJson('group_order_created_fail', 200, $data);
        }


        if ($groupOrder->getUser()->getId() == $user->getId()) {
            $groupOrder->setPending();
        } else {
            $groupOrder->setCompleted($user);
        }

        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        if ($groupOrder->isPending()) {

            $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

            $formId = $groupUserOrder->getPrePayId();
            $templateId = "9RLVQOC7gP3qJipiFX2efKvz2oSYuJRs0dDaY2UeDIA";
            $page = "pages/group/index?id=" . $groupOrderId;
            $toUser = $groupUserOrder->getUser()->getWxOpenId();
            $data = ['keyword1' => ['value' => $groupOrder->getProduct()->getTitle()],
                'keyword2' => ['value' => $groupOrder->getProduct()->getPrice()],
                'keyword3' => ['value' => 1],
                'keyword4' => ['value' => '2019-01-01 12:12:12']];
            $emphasisKeyword = "keyword3.DATA";

            $wxApi = new WxCommon($this->getLog());
            $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);

            //$command = new NotifyPendingGroupOrderCommand($groupOrder->getId());
            //$this->getCommandBus()->handle($command);
        } else if ($groupOrder->isCompleted()) {
            $command = new NotifyCompletedGroupOrderCommand($groupOrder->getId());
            //$this->getCommandBus()->handle($command);
        }

        $data = [
            'groupOrder' => $groupOrder->getArray()
        ];
        return $this->responseJson('group_order_created_success', 200, $data);

    }

    /**
     * 查看拼团详情
     *
     * @Route("/groupOrder/view", name="viewGroupOrder", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function viewAction(Request $request, GroupOrderRepository $groupOrderRepository) {
        $data = json_decode($request->getContent(), true);
        $groupOrderId = isset($data['groupOrderId']) ? $data['groupOrderId'] : null;

        /**
         * @var GroupOrder $groupOrder
         */
        $groupOrder = $groupOrderRepository->find($groupOrderId);
        $data = [
            'groupOrder' => $groupOrder->getArray()
        ];

        return $this->responseJson('success', 200, $data);
    }
}