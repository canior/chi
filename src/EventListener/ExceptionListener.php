<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/23
 * Time: 21:07
 */

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     * @author zxqc2018
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        //判断是否/appApi 开头的请求 返回json
        if (!empty($event->getRequest()->getRequestUri()) && strpos($event->getRequest()->getRequestUri(), '/appApi') === 0) {
            $response = new JsonResponse();
            $data = [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
            ];

            $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
            $debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ('prod' !== $env));

            //debug模式下输出对应异常信息
            if ($debug) {
                $data['exceptionMsg'] = $exception->getMessage();
            }

            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
                $data['code'] = $exception->getStatusCode();
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $data['msg'] = Response::$statusTexts[$data['code']] ?? 'Internal Server Error';
            $response->setData($data);
            $event->setResponse($response);
        }
    }
}