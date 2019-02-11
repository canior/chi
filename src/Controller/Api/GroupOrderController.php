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
use App\Entity\ShareSource;
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
 *
 * 集call拼团
 *
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
        $url = isset($data['url']) ? $data['url'] : null;

        $user = $this->getWxUser($thirdSession);

        /**
         * @var Product $product
         */
        $product = $this->getEntityManager()->getRepository(Product::class)->find($productId);

        $groupOrder = GroupOrder::factory(GroupOrder::GROUP_GIFT, $user, $product);
        $groupOrder->setPending();
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        $data = [
            'product' => $groupOrder->getProduct()->getArray(),
            'groupOrder' => $groupOrder->getArray(),
            'shareSources' => $this->createGroupOrderShareSource($groupOrder, $url)
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

        if (!$groupOrder->isPending()) {
            return $this->responseJson('success', 302, []);
        }

        $groupUserOrder = $groupOrder->getSlaveGroupUserOrder($user);
        if ($groupUserOrder == null) {
            $groupUserOrder = GroupUserOrder::factory($user, $groupOrder->getProduct(), $groupOrder);
        }
        $groupUserOrder->setPending();
        $groupUserOrder->setPaid();

        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();


        $data = [
            'groupUserOrder' => $groupUserOrder->getArray()
        ];
        return $this->responseJson('success', 200, $data);
    }


    /**
     * //TODO 这个接口是多余的???
     * 拼团到期
     *
     * @Route("/groupOrder/expire", name="expireGroupOrder", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function expireAction(Request $request, GroupOrderRepository $groupOrderRepository) {
        $data = json_decode($request->getContent(), true);
        $groupOrderId =  isset($data['groupOrderId']) ? $data['groupOrderId'] : null;

        $groupOrder = $groupOrderRepository->find($groupOrderId);
        $groupOrder->setExpired();
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();
        return $this->responseJson('success', 200, ['groupOrder' => $groupOrder->getArray()]);
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
        $url = isset($data['url']) ? $data['url'] : null;

        /**
         * @var GroupOrder $groupOrder
         */
        $groupOrder = $groupOrderRepository->find($groupOrderId);

        $data = [
            'product' => $groupOrder->getProduct()->getArray(),
            'groupOrder' => $groupOrder->getArray(),
            'shareSources' => $this->createGroupOrderShareSource($groupOrder, $url)
        ];

        return $this->responseJson('success', 200, $data);
    }
}