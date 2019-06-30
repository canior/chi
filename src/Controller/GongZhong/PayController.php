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
use App\Service\Util\MoneyUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * 普通购买活动订单
     * @Route("/gzhAuth/groupUserOrder/createOfflineCourse", name="gzhCreateOfflineCourseGroupUserOrder", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
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
     * @Route("/gzhAuth/groupUserOrder/pay", name="gzhPayGroupUserOrder", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return JsonResponse
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

        $openId = $groupUserOrder->getUser()->getWxGzhOpenId();

        if (empty($openId)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_GZH_PAY_ID_NOT_EXISTS, []);
        }

        $body = $groupUserOrder->getProduct()->getTitle();

        $groupUserOrder->setPaymentChannel(GroupUserOrder::PAYMENT_CHANNEL_WX_GZH);
        $outTradeNo = $groupUserOrder->makeTraceNo();
        $options = [
            'out_trade_no'     => $outTradeNo, // 订单号
            'total_fee'        => $groupUserOrder->getTotal() * 100, // 订单金额，**单位：分**
            'body'             => $body, // 订单描述
            'spbill_create_ip' => CommonUtil::getUserIp(), // 支付人的 IP
            'openid'           => $openId, // 支付人的 openID
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

    /**
     * 支付同步通知
     * @Route("/gzhAuth/groupUserOrder/notifyPayment", name="gzhNotifyGroupUserOrderPayment", methods="POST")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return JsonResponse
     */
    public function notifyPaymentAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository) : JsonResponse
    {
        $requestProcess = $this->processRequest($request, [
            'groupUserOrderId', 'isPaid'
        ], ['groupUserOrderId']);

        $user = $this->getAppUser();

        $groupUserOrder = $groupUserOrderRepository->find($requestProcess['groupUserOrderId']);

        if (empty($groupUserOrder) || $user !== $groupUserOrder->getUser()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS);
        }

        $data = [
            'nextPageType' => 3,
            'tradingProductId' => 0,
            'needConfirm' => false,
        ];

        //系统课报名处理
        $product = $groupUserOrder->getProduct();
        if ($product->isCourseProduct() && !$product->getCourse()->isOnline()) {
            $course = $product->getCourse();
            if ($course->isSystemSubject()) {
                if ($user->isSystemSubjectPrivilege() || $user->isCompletedPersonalInfo()) {
                    //todo sms通知
                    $data['nextPageType'] = 4;
                }
                //是否需要合伙人确认
                if (!$user->isSystemSubjectPrivilege(false)) {
                    $data['needConfirm'] = true;
                }
            } else if ($course->isThinkingSubject()) {
                if ($course->getPrice() > MoneyUtil::thinkingGeneratePrice()) {
                    $data['nextPageType'] = 2;
                } else {
                    //todo sms通知
                    $data['nextPageType'] = 1;
                }
            } else if ($course->isTradingSubject()) {
                if ($user->isCompletedPersonalInfo()) {
                    $data['nextPageType'] = 4;
                }
            }
        }

        if ($data['nextPageType'] == 3) {
            //查找直通车课程id
            $tradingCourse = FactoryUtil::courseRepository()->findSpecTradingCourse(MoneyUtil::tradeSpecialPrice());
            if (!empty($tradingCourse)) {
                $data['tradingProductId'] = $tradingCourse->getProduct()->getId();
            }
        }
        $data['groupUserOrder'] = $groupUserOrder->getArray();
        return $requestProcess->toJsonResponse($data);
    }

    /**
     * 订单待支付页面
     *
     * @Route("/gzhAuth/groupUserOrder/view", name="gzhViewGroupUserOrder", methods="POST")
     * @return Response
     */
    public function viewAction()
    {
        $requestProcess = $this->processRequest(null, [
            'groupUserOrderId', 'isConfirmView'
        ], ['groupUserOrderId']);
        $groupUserOrderId = $requestProcess['groupUserOrderId'];
        /**
         * @var GroupUserOrder $groupUserOrder
         */
        $groupUserOrder = FactoryUtil::groupUserOrderRepository()->find($groupUserOrderId);

        if (empty($groupUserOrder)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS, []);
        }

        $user = $this->getAppUser();

        //非合伙人确认页面验证user
        if (empty($requestProcess['isConfirmView'])) {
            if ($user !== $groupUserOrder->getUser()) {
                $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS, []);
            }
        }

        $data = [
            'groupUserOrder' => $groupUserOrder->getArray(),
            'needConfirm' => false,
        ];

        if (!empty($requestProcess['isConfirmView'])) {
            $data['hasConfirmPrivilege'] = $groupUserOrder->getUser()->getBianxianTopParentPartnerUpUser() == $user;
        }

        //是否需要确认
        $product = $groupUserOrder->getProduct();
        if ($product->isCourseProduct() && !$product->getCourse()->isOnline() && $product->getCourse()->isSystemSubject() && !$user->isSystemSubjectPrivilege(false)) {
            $data['needConfirm'] = true;
        }
        return $requestProcess->toJsonResponse($data);
    }
}