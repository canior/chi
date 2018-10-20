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
use App\Entity\UserAddress;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\UserRepository;
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


        //TODO 向微信提交支付信息
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $body = "创建开团订单";
        $wxPaymentApi = new WxPayment($this->getLog());
        $result = $wxPaymentApi->getPrepayId($user->getWxOpenId(), $groupUserOrder->getId(), $groupUserOrder->getTotal(), $body);
        $prePayId = $result['prepay_id'];
        $noneStr = $result['none_str'];
        $paySign = $result['sign'];

        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();
        $groupUserOrder->setPrePayId($prePayId);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();


        $data = [
            'timeStamp' => time(),
            'noneStr' => $noneStr,
            'package' => $prePayId,
            'signType' => 'MD5',
            'paySign' => $paySign,
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

        $groupUserOrder = new GroupUserOrder($user);
        $groupOrder->addGroupUserOrder($groupUserOrder);

        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();


        //TODO 向微信提交支付信息


        $data = [
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
        //TODO 检查微信支付状态
        $isPaid = true;
        $userId = 2;
        $groupOrderId = 12;

        $groupOrder = $groupOrderRepository->find($groupOrderId);

        if (!$isPaid) {
            //TODO 未支付不改变任何东西,考虑返回啥
            exit;
        }


        if ($groupOrder->getUser()->getId() == $userId) {
            $groupOrder->setPending();
        } else {
            $user = $userRepository->find($userId);
            $groupOrder->setCompleted($user);
        }

        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

//        if ($groupOrder->isPending()) {
//            $command = new EnqueueCommand(new NotifyPendingGroupOrderCommand($groupOrder->getId()), true);
//            $this->getCommandBus()->handle($command);
//        } else if ($groupOrder->isCompleted()) {
//            $command = new EnqueueCommand(new NotifyCompletedGroupOrderCommand($groupOrder->getId()), true);
//            $this->getCommandBus()->handle($command);
//        }

        $data = [
            'groupOrder' => $groupOrder->getArray()
        ];
        return $this->responseJson('success', 200, $data);

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