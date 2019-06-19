<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/23
 * Time: 20:26
 */

namespace App\Controller\AppApi;

use App\Entity\ProductReview;
use App\Entity\ProductReviewImage;
use App\Repository\FileRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
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
use App\Entity\ProjectBannerMeta;
use App\Repository\GroupOrderRepository;
use App\Repository\FollowRepository;
use App\Entity\Follow;
use App\Repository\CourseRepository;
use App\Repository\TeacherRepository;
use App\Entity\GroupUserOrder;
use App\Entity\Message;
use App\Entity\Product;
use App\Repository\MessageRepository;
use App\Repository\UserAccountOrderRepository;

/**
 * @Route("/auth/member")
 */
class MemberController extends AppApiBaseController
{
    /**
     * @Route("/setNewPhone", name="apiSetNewPhone",  methods={"POST"})
     * @param Request $request
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setNewPhone(Request $request, MessageCodeRepository $messageCodeRepository   )
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
     * @param EncoderFactoryInterface $encoderFactory
     * @param UserManagerInterface $userManager
     * @return @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setNewPwd(Request $request, EncoderFactoryInterface $encoderFactory, UserManagerInterface $userManager)
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
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userInfo(Request $request)
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
     * @Route("/sendCode", name="sendCode",  methods={"POST"})
     * @param Request $request
     * @param EncoderFactoryInterface $encoderFactory
     * @param UserManagerInterface $userManager
     * @return @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendCode(Request $request)
    {
        $data = json_decode($request->getContent(), true );

        // 请求参数验证
        $validator = Validation::createValidator(['allowExtraFields'=>true]);
        $constraint = new Assert\Collection(
            [
                'phone' => [
                    new Assert\Length(['min' => 11,'minMessage'=>'不能低于{{ limit }}个字符'])
                ],
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

        //生产验证码
        $phone = isset($data['phone']) ? $data['phone'] : null;
        $code = rand(1000, 9999);
        $messageCode = new MessageCode();
        $messageCode->setPhone($phone);
        $messageCode->setCode($code);
        $messageCode->settype(MessageCode::UPDATE_INFO);
        $this->getEntityManager()->persist($messageCode);
        $this->getEntityManager()->flush();

        // 发送验证码
        $msgTemplateId = "SMS_167760193";
        $msgData = ['code'=>$code];
        $this->sendSmsMsg($phone, $msgData, $msgTemplateId);

        // 返回
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * @Route("/updateUserInfo", name="updateUserInfo",  methods={"POST"})
     * @param Request $request
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateUserInfo(Request $request, UserManagerInterface $userManager, MessageCodeRepository $messageCodeRepository)
    {
        $data = json_decode($request->getContent(), true );

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $name = isset($data['name']) ? $data['name'] : null;
        $phone = isset($data['phone']) ? $data['phone'] : null;
        $code = isset($data['code']) ? $data['code'] : null;
        $idNum = isset($data['idNum']) ? $data['idNum'] : null;
        $nickname = isset($data['nickname']) ? $data['nickname'] : null;
        $company = isset($data['company']) ? $data['company'] : null;
        $wechat = isset($data['wechat']) ? $data['wechat'] : null;
        $recommanderName = isset($data['recommanderName']) ? $data['recommanderName'] : null;


        // 验证Code
        $messageCode = $messageCodeRepository->findOneBy(['phone' => $data['phone'],'type'=>MessageCode::UPDATE_INFO ]);
        if( $messageCode == null || $messageCode->getCode() != $data['code'] ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR )->toJsonResponse();
        }

        // 更新资料
        if($name){
            $user->setName($name);
        }
        if($phone){
            $user->setPhone($phone);
        }
        if($idNum){
            $user->setIdNum($idNum);
        }
        if($nickname){
            $user->setNickname($nickname);
        }
        if($wechat){
            $user->setWechat($wechat);
        }
        if($company){
            $user->setCompany($company);
        }
        if($recommanderName){
            $user->setRecommanderName($recommanderName);
        }

        try {
            $userManager->updateUser($user, true);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
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
        return CommonUtil::resultData(['address'=>$userAddressesArray])->toJsonResponse();
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
        $isDefault = isset($data['isDefault']) ? $data['isDefault'] : 0;

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


        $userAddress->setName($name)->setPhone($phone)->setIsDefault($isDefault)->setRegion($countyDao)->setAddress($address)->setUpdatedAt(time());
        $this->getEntityManager()->persist($userAddress);
        $this->getEntityManager()->flush();

        // 初始化其他默认地址  
        if( $userAddress->getIsDefault() ){
            $userAddressRepository->setOthersNotDefault($user->getId(),$userAddressId);
        }


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
     * @return Response
     */
    public function qrCcodeAction(Request $request) {

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
        $versions = [
            'app'=>'jqktapp',
            'versions'=>'2.0',
            'title'=>'2.0版本正式发布',
            'info'=>'1.全新视觉设计 2.性能全面提升',
            'url'=>'http://download.jqktapp.com'
        ];

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
    public function accountAction(Request $request, UserAccountOrderRepository $userAccountOrderRepository) {

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
            if( $userAccountOrder->getPaymentStatus() == UserAccountOrder::UNPAID ){
                $userAccountOrderArray['UNPAID'][] = $userAccountOrder->getArray();
            }else{
                $userAccountOrderArray['PAID'][] = $userAccountOrder->getArray();
            }
        }

        $data = [
            'balance' => $accountBalance,
            'withdrawedTotal' => $withdrawedTotal,
            'withdrawingTotal' => $withdrawingTotal,
            'userCommissionAmount'=>$userAccountOrderRepository->getUserCommissionAmount($user)['count'],
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
        // if ($amount <= 0) {
        //     return CommonUtil::resultData( [], ErrorCode::ERROR_GREATER_COUNT)->toJsonResponse();
        // }

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
     * @Route("/groupUserOrders", name="groupUserOrders", methods="POST")
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
        $productCategory = isset($data['productCategory']) ? $data['productCategory'] :'';

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        if ($groupUserOrderStatus == null){
            $groupUserOrderStatus =  array_keys(GroupUserOrder::$statuses);
        }

        $paymentStatusArray = ['paid', 'refunding', 'refunded'];

        $groupUserOrders = $groupUserOrderRepository->findBy(
            [
            'user' => $user,
            'status' => $groupUserOrderStatus,
            'paymentStatus' => $paymentStatusArray
            ], 
            ['id' => 'DESC']
        );

        $groupUserOrdersArray = [];
        foreach ($groupUserOrders as $groupUserOrder) {
            $product = $groupUserOrder->getProduct();
            $courseCategory = $groupUserOrder->getCourse()->getCourseCategory()?$groupUserOrder->getCourse()->getCourseCategory()->getId():'';
            if ($productType == 'product' and !$product->isCourseProduct() and $product->getProductCategory() == Product::CATEGORY_PRODUCT) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            } else if ($productType == 'onlineCourse' and $product->isCourseProduct() and $product->getCourse()->isOnline() and $courseCategory == Product::CATEGORY_ONLINE) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            } else if ($productType == 'offlineCourse' and $product->isCourseProduct() and !$product->getCourse()->isOnline() and $courseCategory == Product::CATEGORY_OFFLINE) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            }else{

            }
        }

        // 返回
        return CommonUtil::resultData( ['groupUserOrders' => $groupUserOrdersArray ] )->toJsonResponse();
    }

    /**
     * 我的拼团列表
     * @Route("/groupOrders", name="groupOrders", methods="POST")
     * @param Request $request
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function groupOrdersAction(Request $request, GroupOrderRepository $groupOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $page = isset($data['page']) ? $data['page'] : 1;


        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        /**
         * @var string $groupOrderStatus pending, completed, expired
         */
        $groupOrderStatus = isset($data['groupOrderStatus']) ? $data['groupOrderStatus'] : null;
        $groupOrderStatusArray = [];
        if ($groupOrderStatus != null) {
            $groupOrderStatusArray[] = $groupOrderStatus;
        }


        $groupOrdersArray = [];

        $groupOrdersQuery = $groupOrderRepository->findGroupOrdersForUserQuery($user->getId(), $groupOrderStatusArray);
        /**
         * @var GroupUserOrder[] $groupOrders
         */
        $groupOrders = $this->getPaginator()->paginate($groupOrdersQuery, $page,self::PAGE_LIMIT);
        foreach ($groupOrders as $groupOrder) {
            $groupOrdersArray[] = $groupOrder->getArray();
        }

        // 返回
        return CommonUtil::resultData( ['groupOrders' => $groupOrdersArray ] )->toJsonResponse();
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
        // $totalValidShareSourceUsers = $user->getSubUsers()->count();

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
            // 'validShareSourceUsersTotal' => $totalValidShareSourceUsers,
            'shareSourceUsersTotal' => $totalShareSourceUsers,
            'shareSourceUsers' => $shareSourceUserArray,
            // 'shareSources' => $this->createUserShareSource($user, $url),
            'bannerMetaArray' => $this->createMySharePageProjectBannerMetas($projectBannerMetaRepository)
        ] )->toJsonResponse();
    }


    /**
     * 我的销售列表
     *
     * 全部，待成团， 待发货， 已发货， 待收货
     *
     * 全部: status = null, paymentStatus in ['paid', 'refunding', 'refunded']
     * 待成团: status = 'created', paymentStatus = 'paid'
     * 待发货: status = 'pending', paymentStatus = 'paid'
     * 已发货：status = 'shipping' paymentStatus = 'paid'
     * 已收货: status = 'delivered' paymentStatus = 'paid'
     *
     * @Route("/saleGroupUserOrders", name="saleGroupUserOrders", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function saleGroupUserOrdersAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $groupUserOrderStatus = isset($data['groupUserOrderStatus']) ? $data['groupUserOrderStatus'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $groupUserOrderStatuses = [];
        if ($groupUserOrderStatus == null) {
            $groupUserOrderStatuses =  [GroupUserOrder::PENDING, GroupUserOrder::SHIPPING, GroupUserOrder::DELIVERED];
        } else {
            $groupUserOrderStatuses = [$groupUserOrderStatus];
        }

        /**
         * @var GroupUserOrder[] $groupUserOrders
         */
        $groupUserOrders = $groupUserOrderRepository->findSupplierGroupUserOrdersQuery($user->getId(), $groupUserOrderStatuses)->getResult();

        $groupUserOrdersArray = [];
        foreach ($groupUserOrders as $groupUserOrder) {
            if (!$groupUserOrder->getProduct()->isCourseProduct()) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            }
        }

        // 返回
        return CommonUtil::resultData( ['groupUserOrders' => $groupUserOrdersArray ] )->toJsonResponse();
    }


    /**
     * 我的名额统计
     * @Route("/children", name="children", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function childrenAction(Request $request) {
        
        $data = json_decode($request->getContent(), true);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData( [
            'user'=>$user->getArray(),
            'totalStock' => $user->getTotalRecommandStock(),//拥有的总名额
            'recommandStock' => $user->getRecommandStock(),//剩余名额
            'usedStock' => $user->getUserAccountOrdersAsRecommander()->count(),//用掉的总名额
        ] )->toJsonResponse();
    }

    /**
     * 我的系统学员已用名额列表
     * @Route("/childrenUse", name="childrenUse", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function childrenUseAction(Request $request) {
        
        $data = json_decode($request->getContent(), true);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 获取用户
        $userStockOrders = $user->getUserRecommandStockOrders();

        $childrenArray = [];
        foreach ($userStockOrders as $userStockOrder) {
            $childrenArray[] = $userStockOrder->getArray();
        }

        // 返回
        return CommonUtil::resultData( [
            'children' => $childrenArray,
        ] )->toJsonResponse();
    }


    /**
     * 注册过的课程列表
     * @Route("/courses", name="courses", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function coursesAction(Request $request, UserRepository $userRepository, GroupOrderRepository $groupOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $page = isset($data['page']) ? $data['page'] : 1;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $courseArray = [];
        $courseStudentsQuery = $userRepository->findCourseStudentQuery($user->getId());

        /**
         * @var CourseStudent[] $courseStudents
         */
        $courseStudents = $this->getPaginator()->paginate($courseStudentsQuery, $page, self::PAGE_LIMIT);
        foreach ($courseStudents as $courseStudent) {
            $courseArray[] = $courseStudent->getCourse()->getArray();
        }

        // 返回
        return CommonUtil::resultData( ['courses' => $courseArray ] )->toJsonResponse();
    }


    /**
     * 我的关注列表
     * @Route("/follow", name="follow", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param GroupOrderRepository $groupOrderRepository
     * @return Response
     */
    public function followAction(Request $request, FollowRepository $followRepository,CourseRepository $courseRepository,TeacherRepository $teacherRepository) {

        $data = json_decode($request->getContent(), true);
        $type = isset($data['type']) ? $data['type'] : '';
        $page = isset($data['page']) ? $data['page'] : 1;

        // 查询匹配用户
        $dateType = null;
        if( $type == 'onlineCourse' || $type == 'offlineCourse' ){
            $dateType = Follow::COURSE;
        }
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }


        $followArray = $followRepository->findMyFollow($user->getId(),$dateType,$page,self::PAGE_LIMIT);
        foreach ($followArray as $k => $v) {
            switch ($v['type']) {
                case Follow::COURSE:
                    $course = $courseRepository->find( $v['dataId'] );
                    if($course){
                        if ( $type == 'onlineCourse' && $course->isOnline() ) {
                            $followArray[$k]['course'] = $course->getArray();
                        }else if ( $type == 'offlineCourse' && !$course->isOnline() ) {
                            $followArray[$k]['course'] = $course->getArray();
                        }else{
                            unset($followArray[$k]);
                        }
                    }
                    break;
                case Follow::TEACHER:
                    $teacher = $teacherRepository->find( $v['dataId'] );
                    if($teacher){
                        $followArray[$k]['teacher'] = $teacher->getArray();
                    }
                    break;
                default:
                    break;
            }
        }

        // 返回
        return CommonUtil::resultData(  ['follow'=>$followArray] )->toJsonResponse();
    }


    /**
     * 添加关注 
     * @Route("/postFollow", name="postFollow", methods="POST")
     * @param Request $request
     * @param FollowRepository $followRepository
     * @return Response
     */
    public function postFollowAction(Request $request, FollowRepository $followRepository,CourseRepository $courseRepository) {

        $data = json_decode($request->getContent(), true);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $dataId = isset($data['dataId']) ? $data['dataId'] : null;
        $type = isset($data['type']) ? $data['type'] : null;

        // 是否已经关注
        $had = $followRepository->findBy(['dataId' => $dataId, 'type' => $type, 'user' => $user->getId()]);
        if( $had ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_HAD_FOLLOW )->toJsonResponse();
        }

        // 持久化
        $follow = new Follow();
        $follow->setDataId($dataId)->setType($type)->setUser($user);
        $this->getEntityManager()->persist($follow);
        $this->getEntityManager()->flush();

        // 返回数据
        $followArray = $followRepository->find($follow->getId());
        $course = null;
        switch ($type) {
            case Follow::COURSE:
                $course = $courseRepository->find( $dataId );
                $course = $course->getArray();
                break;
            default:
                break;
        }

        // 返回
        return CommonUtil::resultData( ['follow_id' => $follow->getId(),'course'=>$course ] )->toJsonResponse();
    }

    /**
     * 取消关注 
     * @Route("/delFollow", name="delFollow", methods="POST")
     * @param FollowRepository $followRepository
     * @param Request $request
     * @return Response
     */
    public function delFollowAction(Request $request, FollowRepository $followRepository) {

        $data = json_decode($request->getContent(), true);
        $id = isset($data['id']) ? $data['id'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $follow = $followRepository->find($id);
        if( !$follow ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_FOLLOW_NOTFIND )->toJsonResponse();
        }

        $this->getEntityManager()->remove($follow);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['follow' => $follow ] )->toJsonResponse();
    }

    /**
     * 我的消息列表
     * @Route("/message", name="message", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function messageAction(Request $request, MessageRepository $messageRepository,GroupUserOrderRepository $groupUserOrderRepository) {

        $data = json_decode($request->getContent(), true);
        $page = isset($data['page']) ? $data['page'] : 1;
        $checkStatus = isset($data['checkStatus']) ? $data['checkStatus'] : '';

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $messageQuery = $messageRepository->findOrderMessageQuery($user->getId(),Message::TYPE_GROUP_USER_ORDER);
        $messageArrays = $this->getPaginator()->paginate($messageQuery, $page, self::PAGE_LIMIT);

        $courseArray = [];
        foreach ($messageArrays as $messageArray) {
            //
            $item = $messageArray->getArray();
            $groupUserOrder = $groupUserOrderRepository->find( $messageArray->getDataId() );
            $item['groupUserOrder'] = $groupUserOrder->getArray();
            if( $checkStatus ){
                if(  $groupUserOrder->getCheckStatus() ){
                    $courseArray[] = $item;
                }
            }else{
                if(  !$groupUserOrder->getCheckStatus() ){
                    $courseArray[] = $item;
                }
            }
        }

        // 返回
        return CommonUtil::resultData(  ['messageArray'=>$courseArray] )->toJsonResponse();
    }

    /**
     * 修改审核状态
     * @Route("/updateStatus", name="updateStatus", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function updateStatusAction(Request $request,GroupUserOrderRepository $groupUserOrderRepository) {

        $data = json_decode($request->getContent(), true);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $groupOrdersId = isset($data['groupOrdersId']) ? $data['groupOrdersId'] : null;
        $checkStatus = isset($data['checkStatus']) ? $data['checkStatus'] : null;
        $reason = isset($data['reason']) ? $data['reason'] : null;

        // 持久化
        $groupOrder = $groupUserOrderRepository->find( $groupOrdersId );
        $groupOrder->setCheckStatus($checkStatus);
        if($reason){
            $groupOrder->setReason($reason);
        }
        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['groupOrder' => $groupOrder->getArray() ] )->toJsonResponse();
    }


    /**
     * 已读 消息 
     * @Route("/postMessage", name="postMessage", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function postMessageAction(Request $request, MessageRepository $messageRepository) {

        $data = json_decode($request->getContent(), true);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $id = isset($data['id']) ? $data['id'] : null;
        $isRead = isset($data['isRead']) ? $data['isRead'] : null;

        // 是否已经存在
        $message = $messageRepository->find($id);
        if( !$message ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_MESSAGE_NOT_FIND )->toJsonResponse();
        }

        // 持久化
        $message->setIsRead($isRead);
        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['message_id' => $message->getId() ] )->toJsonResponse();
    }

    /**
     * 删除 消息 
     * @Route("/delMessage", name="delMessage", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function delMessageAction(Request $request, MessageRepository $messageRepository) {

        $data = json_decode($request->getContent(), true);
        $id = isset($data['id']) ? $data['id'] : null;

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        $message = $messageRepository->find($id);
        if( !$message ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_MESSAGE_NOT_FIND )->toJsonResponse();
        }

        $this->getEntityManager()->remove($message);
        $this->getEntityManager()->flush();

        // 返回
        return CommonUtil::resultData( ['message' => $message ] )->toJsonResponse();
    }

    /**
     * 生产桌号 测试方法
     * @Route("/createUserTable", name="createUserTable", methods="POST")
     * @param Request $request
     * @param MessageRepository $messageRepository
     * @return Response
     */
    public function createUserTableAction(Request $request,GroupUserOrderRepository $groupUserOrderRepository){

        $data = json_decode($request->getContent(), true);
        $groupUserOrder = $groupUserOrderRepository->find('400024939');

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 生成桌号
        $user_table = $this->getUserTable($groupUserOrder,$groupUserOrderRepository,$user);
        if( !$user_table ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_ORDER_TABLE_CREATE_FAIL )->toJsonResponse();
        }


        // 保存桌号
        $groupUserOrder->setTableNo($user_table);
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();


        //创建供应商消息
        $msg = new Message();
        $msg->setTitle('报名消息确认')->setContent('名称为'.$user->getNickname().'的用户，报名了线下系统课程课程的并线上支付了'.$groupUserOrder->getTotal().'元的会务费，请确认其是否完成系统学员身份的升级')->setUser($user->getParentUser());
        $this->getEntityManager()->persist($msg);
        $this->getEntityManager()->flush();


        //发送短信 TODO
        // $sms = new AliSms();
        // $data = [];
        // $sms->send($use->getMobile(), $data,'XXX');
        // if( !$res['succes'] ){
        //     return $res;
        // }

        // 返回
        return CommonUtil::resultData(  ['groupUserOrder'=>$groupUserOrder->getArray()] )->toJsonResponse();
    }

    /**
     * 添加或修改评论
     * @Route("/groupUserOrder/review", name="appUpdateProductReview", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProductReviewRepository $productReviewRepository
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param FileRepository $fileRepository
     * @return JsonResponse
     */
    public function updateProductReviewAction(Request $request, ProductRepository $productRepository, ProductReviewRepository $productReviewRepository, GroupUserOrderRepository $groupUserOrderRepository, FileRepository $fileRepository) : JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $productReviewId = isset($data['productReviewId']) ? $data['productReviewId'] : null;
        $groupUserOrderId = isset($data['groupUserOrderId']) ? $data['groupUserOrderId'] : null;
        $productId = isset($data['productId']) ? $data['productId'] : null;
        $rate = isset($data['rate']) ? $data['rate'] : null;
        $review = isset($data['review']) ? $data['review'] : null;
        $reviewImageFileIds = isset($data['imageIds']) ? $data['imageIds'] : [];
        $user = $this->getAppUser();

        $productReview = null;
        if ($productReviewId) {
            $productReview = $productReviewRepository->find($productReviewId);
        }

        if ($productReview == null) {
            $productReview = new ProductReview();
        }

        $groupUserOrder = null;
        if ($groupUserOrderId) {
            $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);
            $productReview->setGroupUserOrder($groupUserOrder);
            $productReview->setProduct($groupUserOrder->getProduct());
            $user = $groupUserOrder->getUser();
        } else {
            /**
             * @var Product $product
             */
            $product = $productRepository->find($productId);
            $productReview->setProduct($product);
        }

        $productReview->setRate($rate);
        $productReview->setReview($review);
        $productReview->setUser($user);

        foreach ($reviewImageFileIds as $fileId) {
            $file = $fileRepository->find($fileId);
            $productReviewImage = new ProductReviewImage();
            $productReviewImage->setImageFile($file);
            $productReviewImage->setProductReview($productReview);
            $productReview->addProductReviewImage($productReviewImage);
        }

        $this->getEntityManager()->persist($productReview);

        if ($groupUserOrder) {
            $groupUserOrder->addProductReview($productReview);
            $this->getEntityManager()->persist($groupUserOrder);
        }

        $this->getEntityManager()->flush();

        return CommonUtil::resultData()->toJsonResponse([
            'productReview' => $productReview->getArray()
        ]);
    }

    /**
     * 获取我的评论列表
     * @Route("/reviews", name="my_product_reviews", methods={"POST"})
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProductReviewRepository $productReviewRepository
     * @author zxqc2018
     * @return JsonResponse
     */
    public function getMyAllProductReviews(Request $request, ProductRepository $productRepository, ProductReviewRepository $productReviewRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'productId', 'type'
        ]);
        $user = $this->getAppUser();
        $product = null;
        if (!empty($requestProcess['productId'])) {
            $product = $productRepository->find($requestProcess['productId']);

            if (empty($product)) {
                $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS, []);
            }
        }

        $where = [
            'user' => $user,
        ];

        if (!empty($product)) {
            $where['product'] = $product;
        }

        if (!empty($requestProcess['type'])) {
            if (!in_array($requestProcess['type'], array_keys(ProductReview::$statuses))) {
                $requestProcess->throwErrorException(ErrorCode::ERROR_PARAM_NOT_ALL_EXISTS, []);
            }
            $where['status'] = $requestProcess['type'];
        }

        $productReviews = $productReviewRepository->findBy($where);

        return $requestProcess->toJsonResponse([
            'myProductReviews' => CommonUtil::entityArray2DataArray($productReviews)
        ]);
    }
}