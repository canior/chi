<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 21:41
 */

namespace App\EventListener;


use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    /**
     * Filters the Response.
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        if (!empty($event->getRequest()->getRequestUri()) && CommonUtil::requestUrlStartsWith($event->getRequest(), 'appApi||gongZhong')) {
            $origin = $event->getRequest()->server->get('HTTP_ORIGIN');
            $allowOrigin = [
                'https://gongzhong.zscollege.com.cn',
            ];

            if(CommonUtil::isDebug()){
                $allowOrigin[] = 'http://localhost:8080';
            }

            if (in_array($origin, $allowOrigin)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN, x-requested-with');
                $response->headers->set('Access-Control-Expose-Headers', 'Authorization, authenticated');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
                $response->headers->set('Access-Control-Max-Age', 18000);
                $response->headers->set('content-type', 'application/json; charset=UTF-8');
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }
        }

        $response->prepare($event->getRequest());
    }
}