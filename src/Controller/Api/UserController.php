<?php
namespace App\Controller\Api;

use App\Entity\GroupUserOrder;
use App\Entity\ProductReview;
use App\Entity\ProductReviewImage;
use App\Entity\Region;
use App\Entity\ShareSource;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Repository\FileRepository;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\RegionRepository;
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
     * 获取用户openId
     *
     * @Route("/user/login", name="userLogin", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function loginAction(Request $request, UserRepository $userRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $code = isset($data['code']) ? $data['code'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $nickName = isset($data['nickName']) ? $data['nickName'] : null;
        $avatarUrl = isset($data['avatarUrl']) ? $data['avatarUrl'] : null;
        //$userInfo = isset($data['userInfo']) ? json_decode($data['userInfo'], true) : null;

        $user = null;
        $msg = "";
        if ($thirdSession) {
            $user = $this->getWxUser($thirdSession);
        }

        if ($user != null) {
            $msg = 'login_success';
        } else {
            $wxApi = new WxCommon($this->getLog());
            $result = $wxApi->getSessionByCode($code);

            if ($result['status']) {
                $openId = $result['data']['openid'];
                $user = $userRepository->findOneBy(['wxOpenId' => $openId]);
                if ($user == null) {
                    $user = new User();
                    $user->setUsername($nickName);
                    $user->setUsernameCanonical($nickName);
                    $user->setEmail($openId . '@qq.com');
                    $user->setEmailCanonical($openId . '@qq.com');
                    $user->setPassword("IamCustomer");
                    $user->setNickname($nickName);
                    $user->setAvatarUrl($avatarUrl);
                    $user->setWxOpenId($openId);
                    $this->getEntityManager()->persist($user);
                    $this->getEntityManager()->flush();
                }
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
     * @Route("/user/groupUserOrders/{groupUserOrderId}", name="myGroupUserOrder", methods="GET")
     * @param Request $request
     * @param $groupUserOrderId
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function getGroupUserOrderAction(Request $request, $groupUserOrderId, GroupUserOrderRepository $groupUserOrderRepository) : Response {
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
     * @Route("/user/groupUserOrders/{groupUserOrderId}", name="updateMyGroupUserOrder", methods="POST")
     * @param Request $request
     * @param $groupUserOrderId
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function updateGroupUserOrderAction(Request $request, $groupUserOrderId, GroupUserOrderRepository $groupUserOrderRepository) : Response {
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
     * @Route("/user/groupUserOrders/{groupUserOrderId}/review", name="updateProductReview", methods="POST")
     * @param Request $request
     * @param $groupUserOrderId
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param FileRepository $fileRepository
     * @return Response
     */
    public function updateProductReviewAction(Request $request, $groupUserOrderId, GroupUserOrderRepository $groupUserOrderRepository, FileRepository $fileRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $rate = isset($data['rate']) ? $data['rate'] : null;
        $review = isset($data['review']) ? $data['review'] : null;
        $reviewImageFileIds = isset($data['imageIds']) ? $data['imageIds'] : [];

        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        $productReview = null;
        $productReviews = $groupUserOrder->getProductReviews();
        if (empty($productReviews)) {
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
    public function addUserAddressAction(Request $request, UserAddressRepository $userAddressRepository, RegionRepository $regionRepository): Response {

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
        $isDefault = isset($data['isDefault']) ? $data['isDefault'] : null;

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
        $userAddress->setName($name)->setPhone($phone)->setRegion($countyDao)->setAddress($address)->setIsDefault($isDefault)->setUpdatedAt(time());
        $this->getEntityManager()->persist($userAddress);
        $this->getEntityManager()->flush();

        //TODO: 若$isDefault=true则要检查其它地址中有无已设为默认的，有要去掉，不然就会有多个默认

        return $this->responseJson('success', 200, [
            'userAddresses' => $userAddress->getArray()
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
    public function updateUserAddressAction(Request $request, UserAddressRepository $userAddressRepository): Response {

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
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function createShareSource(Request $request, ProductRepository $productRepository) : Response {

        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $productId = isset($data['productId']) ? $data['productId'] : null;
        $shareSourceType = isset($data['shareSourceType']) ? $data['shareSourceType'] : null;

        $user = $this->getWxUser($thirdSession);
        $product = null;
        $redirect = null;
        $bannerFile = null;

        if ($productId == null) { //分享用户相关
            if ($shareSourceType == 'refer') {
                $title = "";
                $page = "";

            } else if ($shareSourceType == 'quan') {

            }
        } else { //分享产品相关
            $product = $productRepository->find($productId);
            if ($shareSourceType == 'refer') {

            } else if ($shareSourceType == 'quan') {

            }
        }


        $shareSource = new ShareSource();

//        $shareSource->setUser($user);
//        $shareSource->setProduct($product);
//
//        $shareSourceId = $shareSource->getId();
//        $page = $redirect . "?shareSourceId=" . $shareSourceId;
//        $shareSource->setPage($page);
//
//        $shareSource->setTitle($title);
//        $shareSource->setBannerFile($bannerFile);
//
//        $user->addShareSource($shareSource);
//        $this->getEntityManager()->persist($user);

        return $this->responseJson('success', 200, [
            'shareSource' => $shareSource->getArray()
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

}