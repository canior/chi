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
            'phone', 'code', 'openid', 'unionid', 'shareSourceId','productId', 'url'
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
            $messageCode = FactoryUtil::messageCodeRepository()->findOneBy(['phone' => $requestProcess['phone'],'type'=> MessageCode::LOGIN]);
            if(empty($messageCode)|| $messageCode->getCode() != $requestProcess['code'] ){
                $requestProcess->throwErrorException(ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR, []);
            }
        }

        // 查询匹配用户
        $user = FactoryUtil::userRepository()->findOneBy(['phone' => $requestProcess['phone']]);
        $flushFlag = false;
        if (empty($user)) {
            $this->getLog()->info("creating user for unionid" . $requestProcess['phone']);
            $user = new User();
            $user->setUsername($requestProcess['phone']);
            $user->setPhone($requestProcess['phone']);
            $user->setUsernameCanonical($requestProcess['phone']);
            $user->setEmail($requestProcess['phone'] . '@qq.com');
            $user->setEmailCanonical($requestProcess['phone'] . '@qq.com');
            $user->setPassword("IamCustomer");
            $user->setLastLoginTimestamp(time());

            $userStatistics = new UserStatistics($user);
            $user->addUserStatistic($userStatistics);
            $user->setWxUnionId($requestProcess['unionid']);
            $user->setWxGzhOpenId($requestProcess['openid']);
            $user->info('created user ' . $user);
            $flushFlag = true;
        } else {
            if (empty($user->getWxUnionId())) {
                $user->setWxUnionId($requestProcess['unionid']);
                $flushFlag = true;
            }

            if (empty($user->getWxGzhOpenId())) {
                $user->setWxGzhOpenId($requestProcess['openid']);
                $flushFlag = true;
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
            'code', 'shareSourceId','productId','url'
        ], ['code']);

        $code = $requestProcess['code'];
        ConfigParams::getLogger()->info("wxGzh user code = " . $code);

        $gzhWeChatProcess = FactoryUtil::gzhWeChatProcess();

        $openIdInfo = $gzhWeChatProcess->getOpenidByCode($code);

        if (empty($openIdInfo)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_WX_OPENID_WITH_CODE, []);
        }

        ConfigParams::getLogger()->info ("get wx user response for code [" . $code . "]: ", $openIdInfo);

        $openId = $openIdInfo['openid'];
        $unionId = $openIdInfo['unionid'];

        $user = FactoryUtil::userRepository()->findOneBy(['wxUnionId' => $unionId]);
        $this->getLog()->info("found user " . $user == null ? 'true' : 'false');

        $data = [
            'openid' => $openId,
            'unionid' => $unionId,
            'user' => CommonUtil::obj2Array($user),
            'token' => '',
        ];

        if (!empty($user)) {
            $data['token'] = $JWTTokenManager->create($user);

            //添加shareSourceUser
            if ($requestProcess['shareSourceId']) {
                FactoryUtil::shareSourceProcess()->addShareSourceUser($requestProcess['shareSourceId'], $user);
            }
        }

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

        //实名并且是系统学院需要生成桌号
        $this->supplySystemTableNo($user);

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
        $confirmResult = FactoryUtil::partnerAssistantProcess()->partnerConfirmSystemUser($user, $requestProcess['groupUserOrderId']);
        return $confirmResult->toJsonResponse();
    }
}