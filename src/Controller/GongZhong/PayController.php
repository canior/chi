<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 16:41
 */

namespace App\Controller\GongZhong;


use App\Service\Pay\Pay;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PayController extends GongZhongBaseController
{
    /**
     * 支付后异步订单通知
     * @Route("/notify/order/async", name="gzhNotifyAsyncGroupUserOrderPayment")
     * @return Response
     */
    public function notifyPaymentAsyncAction() : Response
    {
        if (CommonUtil::isDebug()) {
            $data = [
                'request' =>$_REQUEST,
                'raw' => file_get_contents('php://input')
            ];

            $this->getLog()->info('notifyGzh||' . $data['raw'], []);
        }
        return CommonUtil::resultData()->toResponse();
    }

    /**
     * @Route("/test/order", name="gzhadsasaadsa")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function getTestPayInfo()
    {
        $requestProcess = $this->processRequest();
        if (CommonUtil::isDebug()) {
            $options = [
                'out_trade_no'     => 'jqtest' . date('YmdHis') . mt_rand(1000, 9999), // 订单号
                'total_fee'        => '0.01', // 订单金额，**单位：分**
                'body'             => '公众号测试', // 订单描述
                'spbill_create_ip' => '127.0.0.1', // 支付人的 IP
                'openid'           => 'oHo3m1Bju3-W08_F62hLpMJnUfVs', // 支付人的 openID
            ];

            $prePayInfo = FactoryUtil::wxPayGzhDriver(Pay::MP_GATEWAY)->apply($options);
            $requestProcess->setData($prePayInfo);
        }
        return $requestProcess->toJsonResponse();
    }
}