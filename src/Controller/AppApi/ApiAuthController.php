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
        return CommonUtil::resultData(['token' => $JWTTokenManager->create($user)])->toJsonResponse();
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
            'phone' => new Assert\Length(['min' => 6, 'max' => 30]),
            'code' => new Assert\Length(['min' => 6, 'max' => 30]),
        ]);
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            return CommonUtil::resultData([], ErrorCode::ERROR_LOGIN_USER_NOT_FIND, 417, (string)$violations)->toJsonResponse();
        }

        // 查询匹配用户
        $user = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 验证Code
        $messageCode = $messageCodeRepository->findOneBy(['phone' => $data['phone'],'type'=>MessageCode::LOGIN ]);
        if( $messageCode == null || $messageCode->getCode() != $data['code'] ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR )->toJsonResponse();
        }

        // 返回
        return CommonUtil::resultData(['token' => $JWTTokenManager->create($user)])->toJsonResponse();
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
            return CommonUtil::resultData([], ErrorCode::ERROR_LOGIN_USER_NOT_FIND, 417, (string)$violations)->toJsonResponse();
        }

        // 查询匹配用户
        $user = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }

        // 验证Code
        $messageCode = $messageCodeRepository->findOneBy(['phone' => $data['phone'],'type'=>MessageCode::LOGIN ]);
        if( $messageCode == null || $messageCode->getCode() != $data['code'] ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_PHONE_OR_CODE_ERROR )->toJsonResponse();
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
}