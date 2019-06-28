<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 13:33
 */

namespace App\Controller\GongZhong;


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
     * @Route("/login", name="gzhLogin")
     * @author zxqc2018
     */
    public function login()
    {
        $requestProcess = $this->processRequest(null);
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
            $requestProcess->throwErrorException(ErrorCode::ERROR_WX_OPENID_LOGIN, []);
        }
        ConfigParams::getLogger()->info ("get wx user response for code [" . $code . "]: ", $openIdInfo->getData());

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