<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 13:33
 */

namespace App\Controller\GongZhong;


use App\Entity\MessageCode;
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
            'phone', 'code', 'openid', 'unionid'
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
        return $requestProcess->toJsonResponse([
            'user' => CommonUtil::obj2Array($user),
            'token' => $JWTTokenManager->create($user)
        ]);
    }

    /**
     * @Route("/login/wx", name="gzhAuthloginWx",  methods={"POST"})
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse
     */
    public function wxLogin(JWTTokenManagerInterface $JWTTokenManager)
    {
        $requestProcess = $this->processRequest(null, [
            'code'
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
        }

        return $requestProcess->toJsonResponse($data);
    }
}