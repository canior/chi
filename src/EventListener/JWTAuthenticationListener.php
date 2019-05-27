<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/24
 * Time: 13:14
 */

namespace App\EventListener;


use FOS\UserBundle\Model\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JWTAuthenticationListener
 * @package App\EventListener
 * @author zxqc2018
 */
class JWTAuthenticationListener
{
    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $data = [
            'code'  => JsonResponse::HTTP_OK,
            'msg' => 'success',
            'data' => [
                'token' => $data['token'],
                'userId' => $user->getId(),
            ],
        ];

        $event->setData($data);
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = [
            'code'  => JsonResponse::HTTP_UNAUTHORIZED,
            'msg' => 'Bad credentials, please verify that your username/password are correctly set',
            'data' => []
        ];

        $response = new JsonResponse($data, JsonResponse::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $data = [
            'code'  => JsonResponse::HTTP_FORBIDDEN,
            'msg' => 'token invalid',
            'data' => []
        ];

        $response = new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }

    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $data = [
            'code'  => JsonResponse::HTTP_FORBIDDEN,
            'msg' => 'token not found',
            'data' => []
        ];

        $response = new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }
}