<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/10
 * Time: 15:58
 */
namespace App\Controller\AppApi;


use App\Entity\CommandMessage;
use App\Entity\CourseOrder;
use App\Entity\GroupUserOrder;
use App\Repository\GroupUserOrderRepository;
use App\Service\ErrorCode;
use App\Service\Pay\Pay;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PayController
 * @package App\Controller\AppApi
 * @author zxqc2018
 */
class PayController extends AppApiBaseController
{
    /**
     * 支付订单
     * @Route("/auth/groupUserOrder/pay", name="appPayGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function payAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'groupUserOrderId', 'type'
        ], ['groupUserOrderId']);
        $user = $this->getAppUser();

        if (!isset(GroupUserOrder::$paymentChannelTexts[$requestProcess['type']])) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_CHANNEL_NO_EXISTS);
        }

        $groupUserOrder = $groupUserOrderRepository->find($requestProcess['groupUserOrderId']);

        if (empty($groupUserOrder) || $groupUserOrder->getUser() !== $user) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS);
        }

        if ($groupUserOrder->isPaid()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_ORDER_ALREADY_PAY, []);
        }

        $body = $groupUserOrder->getProduct()->getTitle();
        if (!$groupUserOrder->getProduct()->isCourseProduct()) {
            $body .= ': ￥490 + 咨询费: ￥1510';
        }

        $groupUserOrder->setPaymentChannel($requestProcess['type']);
        $outTradeNo = $groupUserOrder->makeTraceNo();
        $prePayInfo = [];

        switch ($requestProcess['type']) {
            case GroupUserOrder::PAYMENT_CHANNEL_ALI:
                // 支付参数
                $options = [
                    'out_trade_no' => $outTradeNo, // 商户订单号
                    'total_amount' => $groupUserOrder->getTotal(), // 支付金额
                    'subject'      => $body, // 支付订单描述
                ];

                $result = FactoryUtil::aliPayDriver(Pay::APP_GATEWAY)->apply($options);
                $prePayInfo['payV2Str'] = http_build_query($result);
                break;
            case GroupUserOrder::PAYMENT_CHANNEL_WX:
                // 支付参数
                $options = [
                    'out_trade_no'     => $outTradeNo, // 商户订单号
                    'total_fee'        => $groupUserOrder->getTotal() * 100, // 订单金额，**单位：分**
                    'body'             => $body, // 订单描述
                    'spbill_create_ip' => CommonUtil::getUserIp(), // 支付人的 IP
                ];

                $prePayInfo = FactoryUtil::wxPayDriver(Pay::APP_GATEWAY)->apply($options);

                if (empty($prePayInfo['partnerid'])) {
                    $requestProcess->throwErrorException(ErrorCode::ERROR_WX_PAY_PREPAY_ID, []);
                }
                $prePayId = $prePayInfo['partnerid'];
                $groupUserOrder->setPrePayId($prePayId);
                break;
        }

        $groupUserOrder->setOutTradeNo($outTradeNo);
        $this->entityPersist($groupUserOrder);

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray(),
            'payment' => $prePayInfo,
        ];

        return $requestProcess->toJsonResponse($data);
    }

    /**
     * 支付普通订单成功
     * 1. 微信支付通知
     * 2. 普通购买完成
     * @Route("/auth/groupUserOrder/notifyPayment", name="appNotifyGroupUserOrderPayment", methods="POST")
     * @return JsonResponse
     */
    public function notifyPaymentAction() : JsonResponse
    {
        $res = CommonUtil::resultData();
        return $res->toJsonResponse();
    }

    /**
     * 支付后异步订单通知
     * @Route("/notify/order/async", name="appNotifyAsyncGroupUserOrderPayment")
     * @return Response
     */
    public function notifyPaymentAsyncAction() : Response
    {
        $data = [
            'request' =>$_REQUEST,
            'raw' => file_get_contents('php://input')
        ];

        $this->getLog()->info('notifyTest||' . $data['raw'], []);
        return FactoryUtil::notifyProcess(file_get_contents('php://input'))->process()->toResponse();
    }
}