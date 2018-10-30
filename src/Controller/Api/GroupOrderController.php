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
     * 测试拼团订单
     * @Route("/groupOrder/test", name="testGroupOrder", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request, ProductRepository $productRepository) {
        if ($this->getEnvironment() != 'dev') exit;

        $data = json_decode($request->getContent(), true);
        $productId =  isset($data['productId']) ? $data['productId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $joinerId = isset($data['joinerId']) ? $data['joinerId'] : null;

        $user = $this->getWxUser($thirdSession);
        $product = $productRepository->find($productId);

        //开团
        $groupOrder = new GroupOrder($user, $product);
        $groupOrder->setPending();

        //参团
        /**
         * @var User $joiner
         */
        $joiner = $this->getEntityManager()->getRepository(User::class)->find($joinerId);
        $joinerGroupUserOrder = new GroupUserOrder($joiner, $product);
        $joinerGroupUserOrder->setTotal($product->getGroupPrice());
        $joinerGroupUserOrder->setGroupOrder($groupOrder);
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

        $data = [
            'groupUserOrder' => $groupOrder->getMasterGroupUserOrder()->getArray()
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
            $groupUserOrder = new GroupUserOrder($user, $groupOrder->getProduct());
            $groupUserOrder->setGroupOrder($groupOrder);
            $groupOrder->addGroupUserOrder($groupUserOrder);
        }
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();


        $data = [
            'groupUserOrder' => $groupOrder->getArray()
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
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
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