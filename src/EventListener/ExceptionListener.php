<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/23
 * Time: 21:07
 */

namespace App\EventListener;

use App\Exception\ApiHttpException;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
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
            $resultData = CommonUtil::resultData([], ErrorCode::ERROR_COMMON_UNKNOWN_ERROR);

            //debug模式下输出对应异常信息
            if (CommonUtil::isDebug() && !empty($exception->getMessage())) {
                $resultData['exceptionMsg'] = $exception->getMessage();
                if (!empty($exception->getTrace())) {
                    $trace = $exception->getTrace();
                    array_unshift($trace, [
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'msg' => $exception->getMessage(),
                        'exceptionName' => get_class($exception),
                    ]);
                    $resultData['exceptionTrace'] = $trace;
                }
            }

            if ($exception instanceof ApiHttpException) {
                $resultData->setCode($exception->getCode());
                if (!empty($exception->getData())) {
                    $resultData->setData($exception->getData());
                }
                $resultData->setMsg($exception->getMessage());
            } else if ($exception instanceof HttpExceptionInterface) {
                $resultData->setStatusCode($exception->getStatusCode())->setCode($exception->getStatusCode());
            }

            $event->setResponse($resultData->toJsonResponse());
        }
    }
}