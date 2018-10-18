<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 5:54 PM
 */

namespace App\Controller\Api;

namespace App\Controller\Api;

use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\UserAddress;
use App\Repository\GroupOrderRepository;
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
     * 支付拼团订单结果
     */
    public function notifyPaymentAction() {
        //支付成功后要发通知， 跳转拼团详情页面


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

    public function joinAction() {

    }
}