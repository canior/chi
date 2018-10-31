<?php
namespace App\Controller\Api;

use App\Command\EnqueueCommand;
use App\Command\Notification\NotifyPendingGroupOrderCommand;
use App\Entity\GroupUserOrder;
use App\Entity\ProductReview;
use App\Entity\ProductReviewImage;
use App\Entity\Region;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Entity\UserAddress;
use App\Entity\UserStatistics;
use App\Repository\FileRepository;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\RegionRepository;
use App\Repository\ShareSourceRepository;
use App\Repository\ShareSourceUserRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserAddressRepository;
use App\Repository\UserRepository;
use App\Service\Wx\WxCommon;
use App\Command\File\UploadFileCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class UserController extends BaseController
{

    /**
     * 测试推送信息
     * @Route("/user/test", name="testUser", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request, GroupOrderRepository $groupOrderRepository) {
        if ($this->getEnvironment() != 'dev') exit;
        $data = json_decode($request->getContent(), true);
        $groupOrderId =  isset($data['groupOrderId']) ? $data['groupOrderId'] : null;

        $command = new EnqueueCommand(new NotifyPendingGroupOrderCommand($groupOrderId), true);
        $this->getCommandBus()->handle($command);

        return $this->responseJson('success', 200, []);
    }

    /**
     * 获取用户openId
     *
     * @Route("/user/login", name="userLogin", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function loginAction(Request $request, UserRepository $userRepository) : Response {

        $defaultNickname = '未知用户';
        $defaultAvatarUrl = null;

        $data = json_decode($request->getContent(), true);
        $code = isset($data['code']) ? $data['code'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $nickName = isset($data['nickName']) ? $data['nickName'] : $defaultNickname; //TODO 这里要添加文案
        $avatarUrl = isset($data['avatarUrl']) ? $data['avatarUrl'] : null; //需要一张默认的用户头像
        //$userInfo = isset($data['userInfo']) ? json_decode($data['userInfo'], true) : null;

        $user = null;
        $msg = "";
        if ($thirdSession) {
            $user = $this->getWxUser($thirdSession);
        }

        if ($user != null) {
            $msg = 'login_success';
            $this->getLog()->info("input nickName=" . $nickName . ' and avatarUrl =' . $avatarUrl);
            if ($defaultNickname == $user->getNickname() and $defaultAvatarUrl == $user->getAvatarUrl()) {
                $this->getLog()->info("update user nickname and avatar url");
                $user->setNickname($nickName);
                $user->setAvatarUrl($avatarUrl);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
            }
        } else {
            $wxApi = new WxCommon($this->getLog());
            $result = $wxApi->getSessionByCode($code);

            $this->getLog()->info ("get wx user response for code [" . $code . "]: ", $result);
            if ($result['status']) {
                $openId = $result['data']['openid'];
                $user = $userRepository->findOneBy(['wxOpenId' => $openId]);
                $this->getLog()->info("found user " . $user == null ? 'true' : 'false');
                if ($user == null) {
                    $this->getLog()->info("creating user for openId" . $openId);
                    $user = new User();
                    $user->setUsername($openId);
                    $user->setUsernameCanonical($openId);
                    $user->setEmail($openId . '@qq.com');
                    $user->setEmailCanonical($openId . '@qq.com');
                    $user->setPassword("IamCustomer");
                    $user->setWxOpenId($openId);

                    $userStatistics = new UserStatistics($user);
                    $user->addUserStatistic($userStatistics);
                }
                $user->setNickname($nickName);
                $user->setAvatarUrl($avatarUrl);

                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();

                $userId = $user->getId();
                $thirdSession = $userId;//生成我们自己的第三方session

                $msg = "login_success";
            } else {
                $this->getLog()->info(json_encode($result));
                $msg = "login_failed";
            }
        }

        return $this->responseJson($msg, 200, [
            'thirdSession' => $thirdSession,
            'user' => $user->getArray()
        ]);

    }


    /**
     * 我的拼团列表
     * @Route("/user/groupOrders/", name="myGroupOrders", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function getGroupOrdersAction(Request $request, GroupOrderRepository $groupOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        /**
         * @var string $groupOrderStatus pending, completed, expired
         */
        $groupOrderStatus = isset($data['groupOrderStatus']) ? $data['groupOrderStatus'] : null;
        $groupOrderStatusArray = [];
        if ($groupOrderStatus != null) {
            $groupOrderStatusArray[] = $groupOrderStatus;
        }

        $user = $this->getWxUser($thirdSession);

        $groupOrdersArray = [];

        $groupOrders = $groupOrderRepository->findGroupOrdersForUser($user->getId(), $groupOrderStatusArray);

        foreach ($groupOrders as $groupOrder) {
            $groupOrdersArray[] = $groupOrder->getArray();
        }

        return $this->responseJson('success', 200, [
            'groupOrders' => $groupOrdersArray
        ]);
    }

    /**
     * 我的拼团详情
     * @Route("/user/groupOrders/{groupOrderId}", name="myGroupOrder", methods="GET")
     * @param Request $request
     * @param $groupOrderId
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function getGroupOrderAction(Request $request, $groupOrderId, GroupOrderRepository $groupOrderRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $groupOrder = $groupOrderRepository->find($groupOrderId);

        return $this->responseJson('success', 200, [
            'groupOrder' => $groupOrder->getArray()
        ]);
    }


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
     * @Route("/user/groupUserOrders/", name="myGroupUserOrders", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function getGroupUserOrdersAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $groupUserOrderStatus = isset($data['groupUserOrderStatus']) ? $data['groupUserOrderStatus'] : null;


        $user = $this->getWxUser($thirdSession);

        if ($groupUserOrderStatus == null)
            $groupUserOrderStatus =  array_keys(GroupUserOrder::$statuses);

        $paymentStatusArray = ['paid', 'refunding', 'refunded'];

        $groupUserOrders = $groupUserOrderRepository->findBy(['user' => $user, 'status' => $groupUserOrderStatus, 'paymentStatus' => $paymentStatusArray]);

        $groupUserOrdersArray = [];
        foreach ($groupUserOrders as $groupUserOrder) {
            $groupUserOrdersArray[] = $groupUserOrder->getArray();
        }

        return $this->responseJson('success', 200, [
            'groupUserOrders' => $groupUserOrdersArray
        ]);
    }

    /**
     * 我的订单详情
     * @Route("/user/groupUserOrder", name="myGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function getGroupUserOrderAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;

        $user = $this->getWxUser($thirdSession);
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        return $this->responseJson('success', 200, [
            'groupUserOrder' => $groupUserOrder->getArray()
        ]);
    }


    /**
     * 确认收货
     * @Route("/user/groupUserOrder/post", name="updateMyGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function updateGroupUserOrderAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);
        $groupUserOrder->setDelivered();
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'groupUserOrder' => $groupUserOrder->getArray()
        ]);
    }

    /**
     * 添加或修改评论
     * @Route("/user/groupUserOrder/review", name="updateProductReview", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param FileRepository $fileRepository
     * @return Response
     */
    public function updateProductReviewAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, FileRepository $fileRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;
        $rate = isset($data['rate']) ? $data['rate'] : null;
        $review = isset($data['review']) ? $data['review'] : null;
        $reviewImageFileIds = isset($data['imageIds']) ? $data['imageIds'] : [];

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        $productReview = null;
        $productReviews = $groupUserOrder->getProductReviews();
        if ($productReviews->isEmpty()) {
            $productReview = new ProductReview();
        } else {
            $productReview = $productReviews[0];
        }

        $productReview->setGroupUserOrder($groupUserOrder);
        $productReview->setProduct($groupUserOrder->getGroupOrder()->getProduct());
        $productReview->setRate($rate);
        $productReview->setReview($review);

        foreach ($reviewImageFileIds as $fileId) {
            $file = $fileRepository->find($fileId);
            $productReviewImage = new ProductReviewImage();
            $productReviewImage->setImageFile($file);
            $productReviewImage->setProductReview($productReview);
            $productReview->addProductReviewImage($productReviewImage);
        }
        $groupUserOrder->addProductReview($productReview);

        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'productReview' => $productReview->getArray()
        ]);
    }



    /**
     * 获取用户收货地址列表
     *
     * @Route("/user/addresses", name="listUserAddresses", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function listUserAddressesAction(Request $request, UserAddressRepository $userAddressRepository): Response {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $user = $this->getWxUser($thirdSession);

        $userAddresses = $userAddressRepository->findBy(['user' => $user, 'isDeleted' => false], ['id' => 'DESC']);

        $userAddressesArray = [];
        foreach($userAddresses as $userAddress) {
            $userAddressesArray[] = $userAddress->getArray();
        }
        return $this->responseJson('success', 200, [
            'userAddresses' => $userAddressesArray
        ]);
    }

    /**
     * 获取用户收货地址详情
     *
     * @Route("/user/address", name="getUserAddress", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function getUserAddressAction(Request $request, UserAddressRepository $userAddressRepository): Response {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $userAddressId = isset($data['userAddressId']) ? $data['userAddressId'] : null;
        $user = $this->getWxUser($thirdSession);

        $userAddress = $userAddressRepository->find($userAddressId);

        return $this->responseJson('success', 200, [
            'userAddresses' => $userAddress->getArray()
        ]);
    }

    /**
     * 添加或更新用户收货地址
     *
     * @Route("/user/address/post", name="addUserAddress", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @param RegionRepository $regionRepository
     * @return Response
     */
    public function updateUserAddressAction(Request $request, UserAddressRepository $userAddressRepository, RegionRepository $regionRepository): Response {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);

        $userAddressId = isset($data['userAddressId']) ? $data['userAddressId'] : null;
        $name = isset($data['name']) ? $data['name'] : null;
        $phone = isset($data['phone']) ? $data['phone'] : null;
        $province = isset($data['province']) ? $data['province'] : null;
        $city = isset($data['city']) ? $data['city'] : null;
        $county = isset($data['county']) ? $data['county'] : null;
        $address = isset($data['address']) ? $data['address'] : null;

        // 查询或新建region
        $provinceDao = $regionRepository->findOneBy(['name' => $province, 'parentRegion' => null]);
        if (!$provinceDao) {
            $provinceDao = new Region();
            $provinceDao->setName($province);
            $this->getEntityManager()->persist($provinceDao);
            $this->getEntityManager()->flush();
        }
        $cityDao = $regionRepository->findOneBy(['name' => $city, 'parentRegion' => $provinceDao]);
        if (!$cityDao) {
            $cityDao = new Region();
            $cityDao->setName($city)->setParentRegion($provinceDao);
            $this->getEntityManager()->persist($cityDao);
            $this->getEntityManager()->flush();
        }
        $countyDao = $regionRepository->findOneBy(['name' => $county, 'parentRegion' => $cityDao]);
        if (!$countyDao) {
            $countyDao = new Region();
            $countyDao->setName($county)->setParentRegion($cityDao);
            $this->getEntityManager()->persist($countyDao);
            $this->getEntityManager()->flush();
        }

        // 查询或新建userAddress
        if ($userAddressId) {
            $userAddress = $userAddressRepository->find($userAddressId);
        } else {
            $userAddress = new UserAddress();
            $userAddress->setUser($user);
        }


        $userAddress->setName($name)->setPhone($phone)->setRegion($countyDao)->setAddress($address)->setUpdatedAt(time());
        $this->getEntityManager()->persist($userAddress);
        $this->getEntityManager()->flush();


        return $this->responseJson('success', 200, [
            'userAddress' => $userAddress->getArray()
        ]);
    }

    /**
     * 删除用户收货地址
     *
     * @Route("/user/address/delete", name="deleteUserAddress", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function deleteUserAddressAction(Request $request, UserAddressRepository $userAddressRepository): Response {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);

        $userAddressId = isset($data['userAddressId']) ? $data['userAddressId'] : null;
        $userAddress = $userAddressRepository->find($userAddressId);
        $userAddress->setIsDeleted(true)->setUpdatedAt(time());
        $this->getEntityManager()->persist($userAddress);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'userAddresses' => $userAddress->getArray()
        ]);
    }

    /**
     * TODO: 需要生产朋友圈图片
     * 创建用户分享源
     *
     * @Route("/user/shareSource/create", name="createShareSource", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function createShareSource(Request $request, ProductRepository $productRepository, GroupOrderRepository $groupOrderRepository) : Response {

        $data = json_decode($request->getContent(), true);

        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $productId = isset($data['productId']) ? $data['productId'] : null;
        $shareSourceType = isset($data['shareSourceType']) ? $data['shareSourceType'] : null;
        $page = isset($data['page']) ? $data['page'] : null;
        $groupOrderId = isset($data['groupOrderId']) ? $data['groupOrderId'] : null;

        $user = $this->getWxUser($thirdSession);
        $product = null;
        $groupOrder = null;
        $bannerFile = null;
        $title = "";

        if ($groupOrderId != null) { //邀请拼团
            $groupOrder = $groupOrderRepository->find($groupOrderId);
            $bannerFile = $groupOrder->getProduct()->getMainProductImage()->getFile();
            if ($shareSourceType == ShareSource::REFER) { //转发用户相关小程序
                $title = $groupOrder->getUser()->getNickname() . '邀请您参团'; //TODO

            } else if ($shareSourceType == ShareSource::QUAN) { //用户相关朋友圈
                $bannerFile = "";
            }
        } else {
            if ($productId != null) { //分享产品相关
                $product = $productRepository->find($productId);
                $title = $product->getTitle();

                if ($shareSourceType == ShareSource::REFER) { //转发产品相关小程序
                    $bannerFile = $product->getMainProductImage() == null ? null : $product->getMainProductImage()->getFile();
                } else if ($shareSourceType == ShareSource::QUAN) { //转发产品相关朋友圈
                    $bannerFile = $product->getMainProductImage() == null ? null : $product->getMainProductImage()->getFile();
                }
            } else { //分享用户相关
                if ($shareSourceType == ShareSource::REFER) { //转发用户相关小程序
                    //TODO
                    $title = $user->getNickname();
                    $bannerFile = 1;
                } else if ($shareSourceType == ShareSource::QUAN) { //用户相关朋友圈
                    //TODO
                    $title = "";
                    $bannerFile = "";
                }
            }
        }

        $shareSource = new ShareSource();
        $shareSource->setUser($user);
        $shareSource->setProduct($product);
        $shareSource->setGroupOrder($groupOrder);

        $shareSourceId = $shareSource->getId();
        if (strpos($page, '?') !== false) {
            $page .= "&shareSourceId=" . $shareSourceId;
        } else {
            $page .= "?shareSourceId=" . $shareSourceId;
        }
        $shareSource->setPage($page);
        $shareSource->setTitle($title);
        $shareSource->setBannerFile($bannerFile);
        $shareSource->setType($shareSourceType);

        $user->getOrCreateTodayUserStatistics()->increaseShareNum(1);
        $user->addShareSource($shareSource);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'shareSource' => $shareSource->getArray()
        ]);

    }

    /**
     * 记录用户来源
     *
     * @Route("/user/shareSource/addUser", name="addShareSource", methods="POST")
     * @param Request $request
     * @param ShareSourceRepository $shareSourceRepository
     * @param ShareSourceUserRepository $shareSourceUserRepository
     * @return Response
     */
    public function addShareSourceUserAction(Request $request, ShareSourceRepository $shareSourceRepository, ShareSourceUserRepository $shareSourceUserRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $shareSourceId = isset($data['shareSourceId']) ? $data['shareSourceId'] : null;

        $user = $this->getWxUser($thirdSession);
        $shareSource = $shareSourceRepository->find($shareSourceId);

        $sharedUser = $shareSourceUserRepository->findOneBy(['user' => $user, 'shareSource' => $shareSource]);
        if ($sharedUser == null) {
            $user->getOrCreateTodayUserStatistics()->increaseChildrenNum(1);
            $this->getEntityManager()->persist($user);
        }

        $shareSourceUser = new ShareSourceUser($shareSource, $user);
        $shareSource->addShareSourceUser($shareSourceUser);
        $this->getEntityManager()->persist($shareSource);

        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, [
            'shareSourceUser' => $shareSourceUser->getArray()
        ]);
    }


    /**
     * 用户评价上传图片
     *
     * @Route("/user/file/upload", name="userFileUpload", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function uploadFileAction(Request $request): Response {

        $thirdSession = $request->request->get('thirdSession');
        $user = $this->getWxUser($thirdSession);

        $files = $request->files;

        $fileId = null;
        $name = null;
        foreach ($files as $file) {
            try {
                $command = new UploadFileCommand($file, $user->getId());
                $fileId = $this->getCommandBus()->handle($command);
                $name = $file->getClientOriginalName();
            } catch (\Exception $e) {
                $this->getLog()->error('upload file failed {error}', ['error' => $e->getMessage()]);
                return $this->response503('upload file failed');
            }
        }

        return $this->responseJson('success', 200, ['fileId' => $fileId]);
    }

    /**
     * 用户收益列表
     *
     * @Route("/user/rewards/list", name="userRewardsList", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function listUserRewardsAction(Request $request) {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $user = $this->getWxUser($thirdSession);

        return $this->responseJson('success', 200, [
            'totalRewards' => $user->getTotalRewards(),
            'children' => $user->getSharedUsersArray()
        ]);
    }


    /**
     * 添加用户访问记录
     *
     * @Route("/user/activity/add", name="addUserActivity", methods="POST")
     * @param Request $request
     * @param UserActivityRepository $userActivityRepository
     * @return Response
     */
    public function addUserActivity(Request $request, UserActivityRepository $userActivityRepository) {
        $data = json_decode($request->getContent(), true);
        $page = isset($data['page']) ? $data['page'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;

        $user = $this->getWxUser($thirdSession);
        $userActivity = new UserActivity($user, $page);
        $user->addUserActivity($userActivity);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $this->responseJson('success', 200, []);
    }

}