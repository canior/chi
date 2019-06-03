<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/23
 * Time: 20:26
 */

namespace App\Controller\AppApi;

use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use App\Service\Util\CommonUtil;
use App\Service\ErrorCode;
use App\Entity\MessageCode;
use App\Repository\MessageCodeRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\UserAddressRepository;
use App\Repository\RegionRepository;
use App\Entity\UserAddress;
use App\Entity\Region;
use App\Entity\UserAccountOrder;
use App\Repository\GroupUserOrderRepository;

/**
 * @Route("/auth/member")
 */
class MemberController extends AppApiBaseController
{
    /**
     * @Route("/setNewPhone", name="apiSetNewPhone",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @param UserManagerInterface $userManager
     */
    public function setNewPhone(
        Request $request, 
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        JWTTokenManagerInterface $JWTTokenManager,
        MessageCodeRepository $messageCodeRepository,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        // 请求参数验证
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'phone' => new Assert\Length(['min' => 6, 'max' => 30]),
            'code' => new Assert\Length(['min' => 6, 'max' => 30]),
        ]);
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            $message = [];
            foreach ($violations as $violation) {
                $message[] = $violation->getMessage();
            }
            return CommonUtil::resultData([], ErrorCode::ERROR_LOGIN_USER_NOT_FIND, implode(',', $message))->toJsonResponse();
        }

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 验证Code
        $messageCode = $messageCodeRepository->findOneBy(['phone' => $data['phone'],'type'=>MessageCode::FORGET ]);
        if( $messageCode == null || $messageCode->getCode() != $data['code'] ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR )->toJsonResponse();
        }

        // 重设手机号
        $user->setPhone($data['phone']);
        try {
            $userManager->updateUser($user, true);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }

        // 返回
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * @Route("/setNewPwd", name="apiSetNewPwd",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @param UserManagerInterface $userManager
     */
    public function setNewPwd(
        Request $request, 
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        JWTTokenManagerInterface $JWTTokenManager,
        MessageCodeRepository $messageCodeRepository,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        // 请求参数验证
        $validator = Validation::createValidator(['allowExtraFields'=>true]);
        
        $constraint = new Assert\Collection(
            [
                'oldPassword' => [
                    new Assert\Length(['min' => 4,'minMessage'=>'不能低于{{ limit }}个字符'])
                ],
                'newPassword' => new Assert\Length(['min' => 6,'minMessage'=>'不能低于{{ limit }}个字符']),
            ]
        );
        $violations = $validator->validate($data, $constraint);  
        if ($violations->count() > 0) {
            $message = [];
            foreach ($violations as $violation) {
                $message[] = $violation->getMessage();
            }
            return CommonUtil::resultData([], ErrorCode::ERROR_LOGIN_USER_NOT_FIND, implode(',', $message))->toJsonResponse();
        }

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 验证旧密码
        $passwordValid = $encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(),$data['oldPassword'], $user->getSalt());
        if( !$passwordValid ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USERNAME_OR_PASSWORD_ERROR )->toJsonResponse();
        }

        // 重设密码
        $user->setPlainPassword($data['newPassword']);
        try {
            $userManager->updateUser($user, true);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }

        // 返回
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * @Route("/userInfo", name="apiUserInfo",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @param UserManagerInterface $userManager
     */
    public function userInfo(
        Request $request, 
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        JWTTokenManagerInterface $JWTTokenManager,
        MessageCodeRepository $messageCodeRepository,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData($user->getArray())->toJsonResponse();
    }


    /**
     * 获取用户收货地址列表
     *
     * @Route("/address", name="myAddress", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addressAction(Request $request, UserAddressRepository $userAddressRepository){

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $userAddresses = $userAddressRepository->findBy(['user' => $user, 'isDeleted' => false], ['id' => 'DESC']);

        $userAddressesArray = [];
        foreach($userAddresses as $userAddress) {
            $userAddressesArray[] = $userAddress->getArray();
        }

        // 返回
        return CommonUtil::resultData($userAddressesArray)->toJsonResponse();
    }

    /**
     * 获取用户收货地址详情
     *
     * @Route("/addressDetail", name="addressDetail", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function addressDetailAction(Request $request, UserAddressRepository $userAddressRepository) {

        $data = json_decode($request->getContent(), true);
        $userAddressId = isset($data['userAddressId']) ? $data['userAddressId'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 查询地址
        $userAddress = $userAddressRepository->find($userAddressId);

        // 返回
        return CommonUtil::resultData(['userAddress'=>$userAddress->getArray()] )->toJsonResponse();
    }

    /**
     * 添加或更新用户收货地址
     *
     * @Route("/addressPost", name="addressPost", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @param RegionRepository $regionRepository
     * @return Response
     */
    public function addressPostAction(Request $request, UserAddressRepository $userAddressRepository, RegionRepository $regionRepository) {

        $data = json_decode($request->getContent(), true );
        
        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

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
            if ($user->getActiveUserAddress()->count() == 0) {
                $userAddress->setIsDefault(true);
            }
        }


        $userAddress->setName($name)->setPhone($phone)->setRegion($countyDao)->setAddress($address)->setUpdatedAt(time());
        $this->getEntityManager()->persist($userAddress);
        $this->getEntityManager()->flush();


        // 返回
        return CommonUtil::resultData(['userAddress' => $userAddress->getArray()])->toJsonResponse();
    }

    /**
     * 删除用户收货地址
     *
     * @Route("/addressDelete", name="addressDelete", methods="POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function addressDeleteAction(Request $request, UserAddressRepository $userAddressRepository) {

        $data = json_decode($request->getContent(), true);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $userAddressId = isset($data['userAddressId']) ? $data['userAddressId'] : null;
        $userAddress = $userAddressRepository->find($userAddressId);
        $userAddress->setIsDeleted(true)->setUpdatedAt(time());
        $this->getEntityManager()->persist($userAddress);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData(['userAddresses' => $userAddress->getArray()])->toJsonResponse();
    }

    /**
     * 查看用户分享二维码
     *
     * @Route("/qrCcode", name="qrCcode", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function qrCcodeAction(Request $request, UserRepository $userRepository) {

        $data = json_decode($request->getContent(), true);
        $url = isset($data['url']) ? $data['url'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData(['shareSources' =>  $this->createUserShareSource($user, $url)])->toJsonResponse();
    }


    /**
     * 获取版本号
     *
     * @Route("/getVersions", name="getVersions", methods="GET")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function getVersionsAction(Request $request) {

        // TODO
        $versions = ['app'=>'jq','versions'=>'1.0'];

        // 返回
        return CommonUtil::resultData($versions)->toJsonResponse();
    }


    /**
     * 查看用户账户
     *
     * @Route("/account", name="account", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function accountAction(Request $request) {

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $accountBalance = $user->getUserAccountTotal();
        $withdrawedTotal = $user->getWithDrawedTotal();
        $withdrawingTotal = $user->getWithDrawingTotal();

        $userAccountOrderArray = [];
        foreach ($user->getUserAccountOrders() as $userAccountOrder) {
            $userAccountOrderArray[] = $userAccountOrder->getArray();
        }

        $data = [
            'balance' => $accountBalance,
            'withdrawedTotal' => $withdrawedTotal,
            'withdrawingTotal' => $withdrawingTotal,
            'userAccountOrders' => $userAccountOrderArray
        ];

        // 返回
        return CommonUtil::resultData($data)->toJsonResponse();
    }


    /**
     * 更新提现的银行账户信息
     * @Route("/bankUpdate", name="bankUpdate", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function bankUpdateAction(Request $request) {

        $data = json_decode($request->getContent(), true);

        // 账户信息
        $bank = isset($data['bank']) ? $data['bank'] : null;
        $bankAccountNumber = isset($data['bankAccountNumber']) ? $data['bankAccountNumber'] : null;
        $bankAccountName = isset($data['bankAccountName']) ? $data['bankAccountName'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $user->setBank($bank);
        $user->setBankAccountNumber($bankAccountNumber);
        $user->setBankAccountName($bankAccountName);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['user' => $user->getArray()] )->toJsonResponse();
    }


    /**
     * 创建提现订单
     *
     * @Route("/accountWithdraw", name="accountWithdraw", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function accountWithdrawAction(Request $request) {
        
        $data = json_decode($request->getContent(), true);
        $amount = isset($data['amount']) ? $data['amount'] : null;
        
        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 大于0
        if ($amount <= 0) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_GREATER_COUNT)->toJsonResponse();
        }

        // 余额
        if ($user->getUserAccountTotal() < $amount) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_GREATER_THEN_ACCOUNT)->toJsonResponse();
        }

        $withdrawOrder = $user->createUserAccountOrder(UserAccountOrder::WITHDRAW, $amount);
        $this->getEntityManager()->persist($withdrawOrder);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['withdrawOrder' => $withdrawOrder->getArray() ] )->toJsonResponse();
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
     * @Route("/groupUserOrders", name="groupUserOrders", methods="GET")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function groupUserOrdersAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $groupUserOrderStatus = isset($data['groupUserOrderStatus']) ? $data['groupUserOrderStatus'] : null;

        /**
         * product, onlineCourse, offlineCourse
         */
        $productType = isset($data['productType']) ? $data['productType'] : false;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        if ($groupUserOrderStatus == null)
            $groupUserOrderStatus =  array_keys(GroupUserOrder::$statuses);

        $paymentStatusArray = ['paid', 'refunding', 'refunded'];

        $groupUserOrders = $groupUserOrderRepository->findBy(['user' => $user, 'status' => $groupUserOrderStatus, 'paymentStatus' => $paymentStatusArray], ['id' => 'DESC']);

        $groupUserOrdersArray = [];
        foreach ($groupUserOrders as $groupUserOrder) {
            $product = $groupUserOrder->getProduct();

            if ($productType == 'product' and !$product->isCourseProduct()) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            } else if ($productType == 'onlineCourse' and $product->isCourseProduct() and $product->getCourse()->isOnline()) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            } else if ($productType == 'offlineCourse' and $product->isCourseProduct() and !$product->getCourse()->isOnline()) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            }
        }

        // 返回
        return CommonUtil::resultData( ['groupUserOrders' => $groupUserOrdersArray ] )->toJsonResponse();
    }

    /**
     * 查看用户最近的分享记录
     *
     * @Route("/shareList", name="shareList", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function shareListAction(Request $request, UserRepository $userRepository) {

        $data = json_decode($request->getContent(), true);
        $url = isset($data['url']) ? $data['url'] : null;
        $page = isset($data['page']) ? $data['page'] : 1;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }
        
        $totalShareSourceUsers = $userRepository->findTotalShareUsers($user->getId(), null);
        $totalValidShareSourceUsers = $user->getSubUsers()->count();

        /* 临时更改 我的分享列出来全部用户头像， 需要加入下拉更新  */
        $shareSourceUsers = $userRepository->findShareUsers($user->getId(), null, $page, null);
        $shareSourceUserArray = [];
        foreach($shareSourceUsers as $shareSourceUser) {
            $shareSourceUserArray[] = $shareSourceUser->getArray();
        }

        /**
         * @var ProjectBannerMetaRepository $projectBannerMetaRepository
         */
        $projectBannerMetaRepository = $this->getEntityManager()->getRepository(ProjectBannerMeta::class);

        // 返回
        return CommonUtil::resultData( [
            'validShareSourceUsersTotal' => $totalValidShareSourceUsers,
            'shareSourceUsersTotal' => $totalShareSourceUsers,
            'shareSourceUsers' => $shareSourceUserArray,
            'shareSources' => $this->createUserShareSource($user, $url),
            'bannerMetaArray' => $this->createMySharePageProjectBannerMetas($projectBannerMetaRepository)
        ] )->toJsonResponse();
    }
}