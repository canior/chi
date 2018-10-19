<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 5:54 PM
 */

namespace App\Controller\Api;

namespace App\Controller\Api;

use App\Command\EnqueueCommand;
use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\UserAddress;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class GroupOrderController extends BaseController
{
    /**
     * 创建拼团订单
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

        //TODO expiredAt from projectMeta
        $expiredAt = time() + 24*3600;

        $groupOrder = new GroupOrder();
        $groupOrder->setUser($user);
        $groupOrder->setProduct($product);
        $groupOrder->setExpiredAt($expiredAt);

        $groupUserOrder = new GroupUserOrder();
        $groupUserOrder->setUser($user);
        $groupUserOrder->setOrderRewards(10); //TODO 计算出返现金额
        $groupUserOrder->setTotal($product->getPrice());
        $groupUserOrder->setGroupOrder($groupOrder);


        $groupOrder->addGroupUserOrder($groupUserOrder);


        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray()
        ];
        return $this->responseJson('success', 200, $data);
    }

    /**
     * 支付拼团订单
     */
    public function payAction() {

    }

    /**
     * 支付开团订单成功
     * 1. 更新拼团订单状态，过期时间，客户订单状态,支付状态
     * 2. 更新产品库存
     * 3. 发送小程序通知
     *
     * 支付参团订单成功
     * 1.
     *
     * @Route("/groupOrder/paymentNotification", name="notifyGroupOrderPayment", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return void
     */
    public function notifyPaymentAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository){
        //TODO 检查微信支付状态
        $isPaid = true;
        $groupUserOrderId = 3;

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        if (!$isPaid) {
            //TODO 未支付不改变任何东西,考虑返回啥
            exit;
        }

        $groupUserOrder->setPaid();
        $this->getEntityManager()->persist($groupUserOrder);

        $groupOrder = $groupUserOrder->getGroupOrder();
        $product = $groupOrder->getProduct();
        $product->decreaseStock();
        $this->getEntityManager()->persist($product);

        if ($groupUserOrder->isMasterOrder()) {
            $groupOrder->setExpiredAt(time()+24*3600); //TODO 计算过期时间
            $groupOrder->setPending();
        } else {
            $groupOrder->setCompleted();
        }
        $this->getEntityManager()->persist($groupOrder);

        $this->getEntityManager()->flush();

        $command = new EnqueueCommand(new SendPaymentSuccessfulNotificationCommand($groupUserOrderId), true);
        $this->getCommandBus()->handle($command);
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

    /**
     * 邀请好友拼团,转发小程序
     */
    public function shareAction() {

    }

    /**
     * 团员参团
     */
    public function joinAction() {

    }
}