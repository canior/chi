<?php
namespace App\Controller\Api;

use App\Entity\Region;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
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
        $user = $this->getWxUser($thirdSession);

        $groupOrdersArray = [];
        $groupOrders = $groupOrderRepository->findBy(['user' => $user]);
        foreach ($groupOrders as $groupOrder) {
            $groupOrdersArray[] = $groupOrder->getArray();
        }

        return $this->responseJson('success', 200, [
            'groupOrders' => $groupOrdersArray
        ]);
    }

    /**
     * 我的拼团
     * @Route("/user/groupOrders/{groupOrderId}", name="myGroupOrder", methods="GET")
     * @param Request $request
     * @param $groupOrderId
     * @param GroupOrderRepository $groupOrderRepository
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function getGroupOrderAction(Request $request, $groupOrderId, GroupOrderRepository $groupOrderRepository, GroupUserOrderRepository $groupUserOrderRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $user = $this->getWxUser($thirdSession);
        $groupOrder = $groupOrderRepository->find($groupOrderId);

        $groupUserOrder = $groupUserOrderRepository->find(['user' => $user, 'groupOrder' => $groupOrder]);
        $groupUserOrderArray = $groupUserOrder->getArray();

        return $this->responseJson('success', 200, [
            'groupUserOrder' => $groupUserOrderArray
        ]);
    }

    /**
     * 确认收货
     * @Route("/user/groupOrders/{groupOrderId}", name="updateMyGroupOrder", methods="PUT")
     * @param Request $request
     * @param $groupOrderId
     * @param GroupOrderRepository $groupOrderRepository
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function updateGroupOrderAction(Request $request, $groupOrderId, GroupOrderRepository $groupOrderRepository, GroupUserOrderRepository $groupUserOrderRepository) : Response {
        return $this->responseJson('success', 200, []);
    }

    /**
     * 添加评论
     * @Route("/user/groupOrders/{groupOrderId}", name="updateMyGroupOrder", methods="GET")
     * @param Request $request
     * @param $groupOrderId
     * @param GroupOrderRepository $groupOrderRepository
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function addProductReviewAction(Request $request, $groupOrderId, GroupOrderRepository $groupOrderRepository, GroupUserOrderRepository $groupUserOrderRepository) : Response {
        return $this->responseJson('success', 200, []);
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
     * 获取用户收货地址
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