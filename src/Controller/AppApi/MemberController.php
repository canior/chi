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
}