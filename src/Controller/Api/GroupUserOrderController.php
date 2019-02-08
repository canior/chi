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
use App\Command\Notification\NotifyOrderRewardsSentCommand;
use App\Command\Notification\NotifyPendingGroupOrderCommand;
use App\Entity\CourseStudent;
use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\UserLevel;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\UserAddressRepository;
use App\Repository\UserRepository;
use App\Service\Wx\WxCommon;
use App\Service\Wx\WxPayment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ShareSource;
use App\Entity\ProjectShareMeta;

/**
 * @Route("/wxapi")
 */
class GroupUserOrderController extends BaseController
{

    /**
     * 订单待支付页面
     *
     * @Route("/groupUserOrder/view", name="viewGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function viewAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) {
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
            'shareSources' => $this->createShareSource($user, $groupUserOrder->getProduct(), $url)
        ];

        return $this->responseJson('success', 200, $data);
    }

    /**
     * 选择订单地址
     *
     * @Route("/groupUserOrder/confirmAddress", name="groupUserOrderConfirmAddress", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function confirmAddressAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, UserAddressRepository $userAddressRepository) {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $addressId = isset($data['addressId']) ? $data['addressId'] : null;
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;

        $user = $this->getWxUser($thirdSession);
        $userAddress = $userAddressRepository->find($addressId);

        //每次订单的地址自动成为用户默认地址
        $user->setDefaultAddress($userAddress);
        $this->getEntityManager()->persist($user);

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);
        $groupUserOrder->setUserAddress($userAddress);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'groupUserOrder' => $groupUserOrder->getArray()
        ]);

    }

    /**
     * 普通购买创建用户订单
     * @Route("/groupUserOrder/create", name="createGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param ProductRepository $productRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, ProductRepository $productRepository) {
        $data = json_decode($request->getContent(), true);
        $productId =  isset($data['productId']) ? $data['productId'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $product = $productRepository->find($productId);

        $groupUserOrder = GroupUserOrder::factory($user, $product);
        $user->createUpgradeUserOrder(UserLevel::ADVANCED, $groupUserOrder);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'groupUserOrder' => $groupUserOrder->getArray()
        ]);
    }

    /**
     * 支付订单
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
     * 支付普通订单成功
     * 1. 微信支付通知
     * 2. 普通购买完成
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

        $groupUserOrder->setPending();
        $groupUserOrder->setPaid();
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray()
        ];
        return $this->responseJson('success', 200, $data);

    }

    /**
     * 返回转发和朋友圈的shareSource
     *
     * @param User $user
     * @param Product $product
     * @param $page
     * @return array
     */
    private function createShareSource(User $user, Product $product, $page) {

        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referProductShare
         */
        $referProductShare = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_PRODUCT]);

        $shareSources = [];

        //产品信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user'=> $user, 'product' => $product, 'type' => ShareSource::REFER_PRODUCT]);
        if ($referShareSource == null) {
            $referShareSource = ShareSource::factory(ShareSource::REFER_PRODUCT, $page, $user, null, $referProductShare->getShareTitle(), $product);
            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //产品信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'product' => $product, 'type' => ShareSource::QUAN_PRODUCT]);
        if ($quanShareSource == null) {
            $quanShareSource = ShareSource::factory(ShareSource::QUAN_PRODUCT, $page, $user);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'shareSourceId=' . $quanShareSource->getId(), $page, true);

            $bannerFile = null;
            if ($product->getMainProductImage() and $product->getMainProductImage()->getFile()) {
                $bannerFile = ImageGenerator::createShareQuanBannerImage($userQrFile, $product->getMainProductImage()->getFile());
            }
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();


        return $shareSources;
    }
}