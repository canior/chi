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
}