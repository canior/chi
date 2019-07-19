<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/24
 * Time: 13:14
 */

namespace App\EventListener;


use App\Entity\User;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use FOS\UserBundle\Model\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
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
     * @param JWTCreatedEvent $event
     * @author zxqc2018
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        /**
         * @var User $user
         */
        $user = $event->getUser();
        $payload['userId'] = !empty($user) ? $user->getId() : 0;
        $payload['username'] = !empty($user) ? $user->getId() : 0;
        $event->setData($payload);
    }

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

        $resultData = CommonUtil::resultData();
        $resultData['token'] = $data['token'];

        $event->setData($resultData->toArray());
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $response = CommonUtil::resultData([], ErrorCode::ERROR_TOKEN_AUTH_FAILURE,'', JsonResponse::HTTP_UNAUTHORIZED)->toJsonResponse();
        $event->setResponse($response);
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = CommonUtil::resultData([], ErrorCode::ERROR_TOKEN_INVALID,'', JsonResponse::HTTP_FORBIDDEN)->toJsonResponse();

        $event->setResponse($response);
    }

    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $response = CommonUtil::resultData([], ErrorCode::ERROR_TOKEN_AUTH_NOT_FOUND,'', JsonResponse::HTTP_FORBIDDEN)->toJsonResponse();
        $event->setResponse($response);
    }
}