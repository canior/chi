<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 13:33
 */

namespace App\Controller\GongZhong;


use App\Entity\MessageCode;
use App\Entity\ShareSource;
use App\Entity\User;
use App\Entity\UserStatistics;
use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\Order\OfflineTableNo;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller\GongZhong
 * @author zxqc2018
 */
class UserController extends GongZhongBaseController
{
    /**
     * @Route("/loginPhone", name="gzhAuthLoginPhone",  methods={"POST"})
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * author zxqc2018
     */
    public function loginPhone(JWTTokenManagerInterface $JWTTokenManager )
    {
        $requestProcess = $this->processRequest(null, [
            'phone', 'code', 'openid', 'unionid', 'shareSourceId','productId', 'url','nickname','avatar', 'upCode'
        ], ['phone', 'code', 'openid', 'unionid']);

        $checkConfig = [
            'phone' => ['len' => 11],
            'code' => ['len' => 4],
        ];

        foreach ($checkConfig as $paramKey => $check) {
            if (!preg_match("#^\d{{$check['len']}}$#", $requestProcess[$paramKey])) {
                $requestProcess->throwErrorException(ErrorCode::ERROR_PARAM_NOT_ALL_EXISTS, ['errorKey' => $paramKey]);
            }
        }

        //验证Code
        if (!CommonUtil::isDebug()) {
            $messageCode = FactoryUtil::messageCodeRepository()->findOneBy(['phone' => $requestProcess['phone'],'type'=> MessageCode::LOGIN], ['id' => 'DESC']);
            if(empty($messageCode)|| $messageCode->getCode() != $requestProcess['code'] ){
                $requestProcess->throwErrorException(ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR, []);
            }
        }

        // 查询匹配用户
        $user = FactoryUtil::userRepository()->findOneBy(['phone' => $requestProcess['phone']]);
        $flushFlag = false;
        if (empty($user)) {
            $this->getLog()->info("creating user for unionid" . $requestProcess['phone']);
            $randPhone = $requestProcess['phone'] . mt_rand(1000,9999);
            $user = new User();
            $user->setUsername($randPhone);
            $user->setPhone($requestProcess['phone']);
            $user->setUsernameCanonical($randPhone);
            $user->setEmail($randPhone . '@qq.com');
            $user->setEmailCanonical($randPhone . '@qq.com');
            $user->setPassword("IamCustomer");
            $user->setLastLoginTimestamp(time());

            $userStatistics = new UserStatistics($user);
            $user->addUserStatistic($userStatistics);
            $user->setWxUnionId($requestProcess['unionid']);
            $user->setWxGzhOpenId($requestProcess['openid']);
            $user->setNickname($requestProcess['nickname']);
            $user->setAvatarUrl($requestProcess['avatar']);
            $user->info('created user ' . $user);
            $flushFlag = true;
            //升级码
            if (!empty($requestProcess['upCode'])) {
                $upgradeCodeInfo = FactoryUtil::userUpgradeCodeRepository()->findOneBy(['code' => $requestProcess['upCode']]);
                if (!empty($upgradeCodeInfo) && empty($upgradeCodeInfo->getUser())) {
                    $upgradeCodeInfo->codeUse($user);
                }
            }
        } else {
            if (empty($user->getWxUnionId())) {
                $user->setWxUnionId($requestProcess['unionid']);
                $flushFlag = true;
            }

            if (empty($user->getWxGzhOpenId())) {
                $user->setWxGzhOpenId($requestProcess['openid']);
                $flushFlag = true;
            }

            if (!empty($requestProcess['avatar']) && (empty($user->getAvatarUrl()) || $user->getAvatarUrl() != $requestProcess['avatar'])) {
                $user->setAvatarUrl($requestProcess['avatar']);
                $flushFlag = true;
            }

            if (empty($user->getNickname())) {
                $user->setNickname($requestProcess['nickname']);
                $flushFlag = true;
            }

            //升级码
            if (!empty($requestProcess['upCode'])) {
                $upgradeCodeInfo = FactoryUtil::userUpgradeCodeRepository()->findOneBy(['code' => $requestProcess['upCode']]);
                if (!empty($upgradeCodeInfo) && empty($upgradeCodeInfo->getUser())) {
                    $upgradeCodeInfo->codeUse($user);
                    $flushFlag = true;
                }
            }
        }

        if ($flushFlag) {
            $this->entityPersist($user);
        }

        //添加shareSourceUser
        if ($requestProcess['shareSourceId']) {
            FactoryUtil::shareSourceProcess()->addShareSourceUser($requestProcess['shareSourceId'], $user);
        }

        $data = [
            'user' => CommonUtil::obj2Array($user),
            'token' => $JWTTokenManager->create($user)
        ];

        $data['shareSources'] = [];
        //产生对应产品的shareSourceId
        if ($requestProcess['productId']) {
            $product = FactoryUtil::productRepository()->find($requestProcess['productId']);
            if (!empty($product)) {
                $shareSourceResult = FactoryUtil::shareSourceProcess()->createShareSource([ShareSource::GZH, ShareSource::GZH_QUAN], ShareSource::PRODUCT, $user, $product, $requestProcess['url'] ?? '');
                $data['shareSources'] = $shareSourceResult->getData();
            }
        }

        return $requestProcess->toJsonResponse($data);
    }

    /**
     * @Route("/login/wx", name="gzhAuthloginWx",  methods={"POST"})
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse
     */
    public function wxLogin(JWTTokenManagerInterface $JWTTokenManager)
    {
        $requestProcess = $this->processRequest(null, [
            'code', 'shareSourceId','productId','url', 'upCode'
        ], ['code']);

        $code = $requestProcess['code'];
//        ConfigParams::getLogger()->info("wxGzh user code = " . $code);
//
//        $gzhWeChatProcess = FactoryUtil::gzhWeChatProcess();
//
//        $openIdInfo = $gzhWeChatProcess->getOpenidByCode($code, false);
//
//        if (empty($openIdInfo)) {
//            $requestProcess->throwErrorException(ErrorCode::ERROR_WX_OPENID_WITH_CODE, []);
//        }
//
//        ConfigParams::getLogger()->info ("get wx user response for code [" . $code . "]: ", $openIdInfo);

        $openIdInfo = [
            'nickname' =>  "茄子粑粑",
            'openid' =>  "oHo3m1OaKop3rIlBjQ6xlLUnA4No",
            'unionid' =>  "o4pLq1TCCG32DlPrWl3O20KUeDeI",
        ];

        $openId = $openIdInfo['openid'];
        $unionId = $openIdInfo['unionid'];
        $nickname = $openIdInfo['nickname'] ?? '未知用户';

        $user = FactoryUtil::userRepository()->findOneBy(['wxUnionId' => $unionId]);
        $this->getLog()->info("found user " . $user == null ? 'true' : 'false');

        $data = [
            'openid' => $openId,
            'unionid' => $unionId,
            'token' => '',
            'nickname' => $nickname,
            'avatar' => $openIdInfo['avatar'] ?? '',
        ];

        if (!empty($user)) {
            $data['token'] = $JWTTokenManager->create($user);

            //添加shareSourceUser
            if ($requestProcess['shareSourceId']) {
                FactoryUtil::shareSourceProcess()->addShareSourceUser($requestProcess['shareSourceId'], $user);
            }

            $flushFlag = false;
            //假如没有公众号openid 则更新
            if (empty($user->getWxGzhOpenId())) {
                $user->setWxGzhOpenId($openId);
                $flushFlag = true;;
            }

            //假如头像有变化则 更新
            if (!empty($data['avatar']) && (empty($user->getAvatarUrl()) || $data['avatar'] != $user->getAvatarUrl())) {
                $user->setAvatarUrl($data['avatar']);
                $flushFlag = true;
            }

            //升级码
            if (!empty($requestProcess['upCode'])) {
                $upgradeCodeInfo = FactoryUtil::userUpgradeCodeRepository()->findOneBy(['code' => $requestProcess['upCode']]);
                if (!empty($upgradeCodeInfo) && empty($upgradeCodeInfo->getUser())) {
                    $upgradeCodeInfo->codeUse($user);
                    $flushFlag = true;
                }
            }

            if ($flushFlag) {
                $this->entityPersist($user);
            }
        }

        $data['user'] = CommonUtil::obj2Array($user);
        $data['shareSources'] = [];
        //产生对应产品的shareSourceId
        if ($requestProcess['productId']) {
            $product = FactoryUtil::productRepository()->find($requestProcess['productId']);
            if (!empty($product)) {
                $shareSourceResult = FactoryUtil::shareSourceProcess()->createShareSource([ShareSource::GZH, ShareSource::GZH_QUAN], ShareSource::PRODUCT, $user, $product, $requestProcess['url'] ?? '');
                $data['shareSources'] = $shareSourceResult->getData();
            }
        }

        return $requestProcess->toJsonResponse($data);
    }

    /**
     * 修改用户资料[实名]
     * @Route("/gzhAuth/updateUserInfo", name="gzhUpdateUserInfo",  methods={"POST"})
     * @author zxqc2018
     */
    public function updateUserInfo()
    {
        $requestProcess = $this->processRequest(null, [
            'name', 'phone','idNum','nickname','company','wechat','recommanderName','code'
        ], ['code']);

        $user =  $this->getAppUser();
        $name = $requestProcess['name'];
        $phone = $requestProcess['phone'];
        $idNum = $requestProcess['idNum'];
        $nickname = $requestProcess['nickname'];
        $company = $requestProcess['company'];
        $wechat = $requestProcess['wechat'];
        $recommanderName = $requestProcess['recommanderName'];


        if (!CommonUtil::isDebug()) {
            //验证Code
            $messageCode = FactoryUtil::messageCodeRepository()->findOneBy([
                'phone' => $phone,
                'type'=>MessageCode::UPDATE_INFO
            ], ['createdAt' => 'DESC']);
            if(empty($messageCode)|| $messageCode->getCode() != $requestProcess['code'] ){
                $requestProcess->throwErrorException(ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR, []);
            }

            //验证过期
            if( $messageCode->getCreatedAt(false)+20*60 < time() ){
                return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_CODE_TIMEOUT )->toJsonResponse();
            }
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

        CommonUtil::entityPersist($user);

        //实名后补上桌号
        OfflineTableNo::supplySystemTableNo($user);

        return $requestProcess->toJsonResponse(['user' => CommonUtil::obj2Array($user)]);
    }

    /**
     *
     * 合伙人确认身份
     * @Route("/gzhAuth/partner/confirm", name="partnerConfirmSystemUser", methods={"POST"})
     * @author zxqc2018
     */
    public function partnerConfirmSystemUser()
    {
        $requestProcess = $this->processRequest(null, [
            'groupUserOrderId'
        ], ['groupUserOrderId']);

        $user = $this->getAppUser();

        $logPath = ConfigParams::getParamWithController('kernel.project_dir'). '/var/log/';

        if (is_dir($logPath)) {
            file_put_contents($logPath .'test.pay.log', json_encode($requestProcess->getData()) . "\n", FILE_APPEND);
        }

        $confirmResult = FactoryUtil::partnerAssistantProcess()->partnerConfirmSystemUser($user, $requestProcess['groupUserOrderId']);
        return $confirmResult->toJsonResponse();
    }

    /**
     * @Route("/getToken", name="getTestToken")
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse
     */
    public function getTestToken(JWTTokenManagerInterface $JWTTokenManager )
    {

        $requestProcess = $this->processRequest(null, ['id'], ['id']);
        $data = [];
        if (CommonUtil::isDebug()) {
            $user = FactoryUtil::userRepository()->find($requestProcess['id']);
            $data = ['token' => $JWTTokenManager->create($user), 'user' => $user->getId()];
        }
        return $requestProcess->toJsonResponse($data);
    }
}