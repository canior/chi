<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 16:41
 */

namespace App\Controller\GongZhong;


use App\Entity\CourseOrder;
use App\Entity\GroupUserOrder;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductRepository;
use App\Service\ErrorCode;
use App\Service\Pay\Pay;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\Request;
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
                'total_fee'        => '1', // 订单金额，**单位：分**
                'body'             => '公众号测试', // 订单描述
                'spbill_create_ip' => '127.0.0.1', // 支付人的 IP
                'openid'           => 'oHo3m1Bju3-W08_F62hLpMJnUfVs', // 支付人的 openID
            ];

            $prePayInfo = FactoryUtil::wxPayGzhDriver(Pay::MP_GATEWAY)->apply($options);
            $requestProcess->setData($prePayInfo);
        }
        return $requestProcess->toJsonResponse();
    }

    /**
     * 普通购买活动订单
     * @Route("/auth/groupUserOrder/createOfflineCourse", name="gzhCreateOfflineCourseGroupUserOrder", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createOfflineCourseAction(Request $request, ProductRepository $productRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'productId'
        ], ['productId']);

        $productId = $requestProcess['productId'];
        $user = $this->getAppUser();
        $product = $productRepository->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        $offlineCourseOrder = CourseOrder::factory($user, $product);

        $this->entityPersist($offlineCourseOrder);

        return $requestProcess->toJsonResponse([
            'groupUserOrder' => $offlineCourseOrder->getArray()
        ]);
    }

    /**
     * 支付订单
     * @Route("/auth/groupUserOrder/pay", name="gzhPayGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function payAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'groupUserOrderId'
        ], ['groupUserOrderId']);
        $user = $this->getAppUser();

        $groupUserOrder = $groupUserOrderRepository->find($requestProcess['groupUserOrderId']);

        if (empty($groupUserOrder) || $groupUserOrder->getUser() !== $user) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS);
        }

        if ($groupUserOrder->isPaid()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_ORDER_ALREADY_PAY, []);
        }

        $where = [
            'product' => $groupUserOrder->getProduct(),
            'user' => $user,
            'paymentStatus' => GroupUserOrder::PAID
        ];

        $paidGroupUserOrder = $groupUserOrderRepository->findOneBy($where);

        if (!empty($paidGroupUserOrder)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_COURSE_ALREADY_PAY, []);
        }

        $body = $groupUserOrder->getProduct()->getTitle();

        $groupUserOrder->setPaymentChannel(GroupUserOrder::PAYMENT_CHANNEL_WX_GZH);
        $outTradeNo = $groupUserOrder->makeTraceNo();
        $options = [
            'out_trade_no'     => $outTradeNo, // 订单号
            'total_fee'        => $groupUserOrder->getTotal() * 100, // 订单金额，**单位：分**
            'body'             => $body, // 订单描述
            'spbill_create_ip' => CommonUtil::getUserIp(), // 支付人的 IP
            'openid'           => 'oHo3m1Bju3-W08_F62hLpMJnUfVs', // 支付人的 openID
        ];

        $prePayInfo = FactoryUtil::wxPayGzhDriver(Pay::MP_GATEWAY)->apply($options);

        if (empty($prePayInfo['prepayid'])) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_WX_PAY_PREPAY_ID, []);
        }
        $prePayId = $prePayInfo['prepayid'];
        $groupUserOrder->setPrePayId($prePayId);

        $groupUserOrder->setOutTradeNo($outTradeNo);
        $this->entityPersist($groupUserOrder);

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray(),
            'payment' => $prePayInfo,
        ];

        return $requestProcess->toJsonResponse($data);
    }
}