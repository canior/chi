<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/23
 * Time: 20:26
 */

namespace App\Controller\AppApi;

use App\Entity\UserStatistics;
use App\Service\Config\ConfigParams;
use App\Service\Document\WeChatDocument;
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
use App\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @Route("/user")
 */
class ApiAuthController extends AppApiBaseController
{
    /**
     * @Route("/register", name="apiAuthRegister",  methods={"POST"})
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register(Request $request, UserManagerInterface $userManager,JWTTokenManagerInterface $JWTTokenManager)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'username' => new Assert\Length(['min' => 6, 'max' => 30]),
            'password' => new Assert\Length(['min' => 6, 'max' => 30]),
        ]);
        $violations = $validator->validate($data, $constraint);

        if ($violations->count() > 0) {
            return CommonUtil::resultData([], ErrorCode::ERROR_LOGIN_USER_NOT_FIND, 417, (string)$violations)->toJsonResponse();
        }
        $username = $data['username'];
        $password = $data['password'];
        $user = new User();
        $user
            ->setUsername($username)
            ->setPlainPassword($password)
            ->setEmail($username . '@qq.com')
            ->setEnabled(true)
            ->setRoles([User::ROLE_CUSTOMER])
            ->setSuperAdmin(false)
        ;
        try {
            $userManager->updateUser($user, true);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }

        return CommonUtil::resultData(['token' => $JWTTokenManager->create($user)])->toJsonResponse();
    }


    /**
     * @Route("/login", name="apiAuthlogin",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login(Request $request, UserRepository $userRepository,EncoderFactoryInterface $encoderFactory,JWTTokenManagerInterface $JWTTokenManager)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        // 请求参数验证
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'username' => new Assert\Length(['min' => 6, 'max' => 30]),
            'password' => new Assert\Length(['min' => 6, 'max' => 30]),
        ]);
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            return CommonUtil::resultData([], ErrorCode::ERROR_LOGIN_USER_NOT_FIND, 417, (string)$violations)->toJsonResponse();
        }

        // 查询匹配用户
        $user = $userRepository->findOneBy(['username' => $data['username']]);
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 验证密码
        $passwordValid = $encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(),$data['password'], $user->getSalt());
        if( !$passwordValid ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USERNAME_OR_PASSWORD_ERROR )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData(['user'=>$user->getArray(),'token' => $JWTTokenManager->create($user)])->toJsonResponse();
    }


    /**
     * @Route("/loginPhone", name="apiAuthLoginPhone",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginPhone(
        Request $request, 
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        JWTTokenManagerInterface $JWTTokenManager,
        MessageCodeRepository $messageCodeRepository
    )
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        // 请求参数验证
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'phone' => new Assert\Length(['min' => 11, 'max' => 30]),
            'code' => new Assert\Length(['min' => 4, 'max' => 30]),
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
        $user = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($user == null) {
            $randPhone = $data['phone'] . mt_rand(1000,9999);
            $this->getLog()->info("creating user for unionid" . $data['phone']);
            $user = new User();
            $user->setUsername($randPhone);
            $user->setPhone($data['phone']);
            $user->setUsernameCanonical($randPhone);
            $user->setEmail($randPhone . '@qq.com');
            $user->setEmailCanonical($randPhone . '@qq.com');
            $user->setPassword("IamCustomer");
            $user->setLastLoginTimestamp(time());

            $userStatistics = new UserStatistics($user);
            $user->addUserStatistic($userStatistics);
            $user->info('created user ' . $user);


            $this->entityPersist($user);
        }

        // 验证Code
        if (!CommonUtil::isDebug()) {
            $messageCode = $messageCodeRepository->findOneBy(['phone' => $data['phone'],'type'=>MessageCode::LOGIN ],['id'=>'DESC']);
            if( $messageCode == null || $messageCode->getCode() != $data['code'] ){
                return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR )->toJsonResponse();
            }

            //验证过期
            if( $messageCode->getCreatedAt(false)+20*60 < time() ){
                return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_CODE_TIMEOUT )->toJsonResponse();
            }
        }

        // 返回
        return CommonUtil::resultData(['user'=>$user->getArray(),'token' => $JWTTokenManager->create($user)])->toJsonResponse();
    }


    /**
     * @Route("/resetPassword", name="apiAuthResetPassword",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param MessageCodeRepository $messageCodeRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @param UserManagerInterface $userManager
     */
    public function resetPassword(
        Request $request, 
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        JWTTokenManagerInterface $JWTTokenManager,
        MessageCodeRepository $messageCodeRepository,
        UserManagerInterface $userManager
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
            'password' => new Assert\Length(['min' => 6, 'max' => 30]),
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
        $user = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 验证Code
        if (!CommonUtil::isDebug()) {
            $messageCode = $messageCodeRepository->findOneBy(['phone' => $data['phone'],'type'=>MessageCode::LOGIN ],['id'=>'DESC']);
            if( $messageCode == null || $messageCode->getCode() != $data['code'] ){
                return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR )->toJsonResponse();
            }

            //验证过期
            if( $messageCode->getCreatedAt(false)+20*60 < time() ){
                return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_CODE_TIMEOUT )->toJsonResponse();
            }
        }

        // 重设密码
        $user->setPlainPassword($data['password']);
        try {
            $userManager->updateUser($user, true);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }

        // 返回
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * @Route("/image/preview/{fileId}", name="appImagePreview",  methods={"GET"})
     * @param int $fileId
     * @return Response
     * @throws
     */
    public function previewImage($fileId = null)
    {
        if (!$fileId) exit;

        /**
         * @var File $file
         */
        $file = $this->getDataAccess()->getDao(File::class, $fileId);
        if ($file->isImage()) {
            $response = new BinaryFileResponse($file->getAbsolutePath());
            return $response;
        } else if ($file->isVideo()) {
            $filepath = $file->getAbsolutePath();
            // Determine file mimetype
            $fp = fopen($filepath, "rb");
            $size = filesize($filepath);
            $length = $size;
            $start = 0;
            $end = $size - 1;
            header('Content-type: video/mp4');
            header("Accept-Ranges: 0-$length");
            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $start;
                $c_end = $end;
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$size");
                    exit;
                }

                if ($range == '-') {
                    $c_start = $size - substr($range, 1);
                } else {
                    $range = explode('-', $range);
                    $c_start = $range[0];
                    $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                }

                $c_end = ($c_end > $end) ? $end : $c_end;

                if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$size");
                    exit;
                }

                $start = $c_start;
                $end = $c_end;
                $length = $end - $start + 1;
                fseek($fp, $start);
                header('HTTP/1.1 206 Partial Content');
            }

            header("Content-Range: bytes $start-$end/$size");
            header("Content-Length: ".$length);

            $buffer = 1024 * 8;

            while(!feof($fp) && ($p = ftell($fp)) <= $end) {
                if ($p + $buffer > $end) {
                    $buffer = $end - $p + 1;
                }
                set_time_limit(0);
                echo fread($fp, $buffer);
                flush();
            }

            fclose($fp);
            exit;

        }
    }

    /**
     * @Route("/login/wx", name="apiAuthloginWx",  methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse
     */
    public function wxLogin(Request $request, UserRepository $userRepository,JWTTokenManagerInterface $JWTTokenManager)
    {
        $requestProcess = $this->processRequest($request, [
            'code'
        ], ['code']);

        $defaultNickname = '未知用户';
        $defaultAvatarUrl = null;
        $code = $requestProcess['code'];
        $this->getLog()->info("wx user code = " . $code);

        $wechat = new WeChatDocument([
            'appid' => ConfigParams::getParamWithController(ConfigParams::JQ_APP_WX_ID),
            'secret' => ConfigParams::getParamWithController(ConfigParams::JQ_APP_WX_SECRET),
        ]);

        $openIdInfo = $wechat->getOpenidByCode($code);

        $this->getLog()->info ("get wx user response for code [" . $code . "]: ", $openIdInfo->getData());

        if (!$openIdInfo->isSuccess()) {
            $openIdInfo->throwErrorException(ErrorCode::ERROR_WX_OPENID_LOGIN, []);
        }
        $accessToken = $openIdInfo['access_token'];
        $openId = $openIdInfo['openid'];
        $unionId = $openIdInfo['unionid'];

        $user = $userRepository->findOneBy(['wxUnionId' => $unionId]);
        $this->getLog()->info("found user " . $user == null ? 'true' : 'false');
        if ($user == null) {
            //提示填写手机号
            $user = new User();
            $user->setUsername($openId);
            $user->setUsernameCanonical($openId);
            $user->setEmail($openId . '@qq.com');
            $user->setEmailCanonical($openId . '@qq.com');
            $user->setPassword("IamCustomer");
            $user->setWxUnionId($unionId);

            $wxUserInfo = $wechat->getWeChatUserInfoByToken($accessToken, $openId);
            $nickName = isset($wxUserInfo['nickname']) ? $wxUserInfo['nickname'] : $defaultNickname;
            $avatarUrl = isset($wxUserInfo['headimgurl']) ? str_replace("http","https",$wxUserInfo['headimgurl']) : null;
            $user->setNickname($nickName);
            $user->setAvatarUrl($avatarUrl);

            return CommonUtil::resultData( ['user'=>$user->getArray(),'token'=>''] )->toJsonResponse();exit();
        }

        return $requestProcess->toJsonResponse(['user' => $user->getArray(),'token' => $JWTTokenManager->create($user)]);
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
                    new Assert\Length(['min' => 11,'minMessage'=>'不能低于{{ limit }}个字符']),
                ],
                'codeType' => [
                    new Assert\Length(['min' => 2,'minMessage'=>'不能低于{{ limit }}个字符']),
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

        //生产验证码
        $phone = isset($data['phone']) ? $data['phone'] : null;
        $codeType = isset($data['codeType']) ? $data['codeType'] : null;

        $code = rand(1000, 9999);
        $messageCode = new MessageCode();
        $messageCode->setPhone($phone);
        $messageCode->setCode($code);
        $messageCode->setType($codeType);
        $this->getEntityManager()->persist($messageCode);
        $this->getEntityManager()->flush();

        // 发送验证码
        $msgTemplateId = "SMS_168345248";
        $msgData = ['code'=>$code];
        $this->sendSmsMsg($phone, $msgData, $msgTemplateId);

        // 返回
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * 获取版本号
     *
     * @Route("/getVersions", name="getVersions", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function getVersionsAction(Request $request) {

        $data = json_decode($request->getContent(), true );
        $type = isset($data['type']) ? $data['type'] : null;

        if( $type == 'IOS' ){
            // TODO
            $versions = [
                'app'=>'ios-app',
                'versions'=>'1.0.0',
                'title'=>'2.0版本正式发布',
                'info'=>'1.全新视觉设计 2.性能全面提升',
                'url'=>'https://apps.apple.com/cn/app/%E6%85%95%E8%AF%BE%E7%BD%91-it%E7%BC%96%E7%A8%8B%E5%9F%B9%E8%AE%ADmooc%E5%85%AC%E5%BC%80%E8%AF%BE%E5%B9%B3%E5%8F%B0/id722179140'
            ];
        }else if( $type == 'ANDRIOD' ){
            // TODO
            $versions = [
                'app'=>'amdriod-app',
                'versions'=>'1.0.1',
                'title'=>'2.0版本正式发布',
                'info'=>'1.全新视觉设计 2.性能全面提升',
                'url'=>'http://download.jqktapp.com'
            ];
        }else{
           $versions = []; 
        }

        // 返回
        return CommonUtil::resultData($versions)->toJsonResponse();
    }
}