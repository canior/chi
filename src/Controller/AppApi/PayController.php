<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/10
 * Time: 15:58
 */
namespace App\Controller\AppApi;


use App\Entity\GroupUserOrder;
use App\Repository\GroupUserOrderRepository;
use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\Pay\Pay;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use App\Service\Util\MoneyUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserAddressRepository;
use App\Repository\ProductRepository;
use App\Entity\CourseOrder;

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
    public function payAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository, UserAddressRepository $userAddressRepository)
    {
        $requestProcess = $this->processRequest($request, 
            ['groupUserOrderId', 'type','addressId'],
            ['groupUserOrderId']
        );
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

        //产品订单 设置地址
        if(isset($requestProcess['addressId'])){
            $addressId = $requestProcess['addressId'];
            $userAddress = $userAddressRepository->find($addressId);
            if (empty($userAddress) || $userAddress->getUser() !== $user) {
                $requestProcess->throwErrorException(ErrorCode::ERROR_ADDRESS_NOT_EXISTS, []);
            }

            //每次订单的地址自动成为用户默认地址
            $userAddress = $userAddressRepository->find($addressId);
            $user->setDefaultAddress($userAddress);
            $this->entityPersist($user, false);

            // 订单地址
            $groupUserOrder->setUserAddress($userAddress);
            $this->entityPersist($groupUserOrder);
        }
        
        $where = [
            'product' => $groupUserOrder->getProduct(),
            'user' => $user,
            'paymentStatus' => GroupUserOrder::PAID
        ];

        if ($groupUserOrder->isUnlockCategoryOrder()) {
            $where['unlockCategory'] = $groupUserOrder->getUnlockCategory();
        }

        $paidGroupUserOrder = $groupUserOrderRepository->findOneBy($where);

        if (!empty($paidGroupUserOrder)) {
            $errorCode = $groupUserOrder->isUnlockCategoryOrder() ? ErrorCode::ERROR_COURSE_CATEGORY_ALREADY_PAY : ErrorCode::ERROR_COURSE_ALREADY_PAY;
            $requestProcess->throwErrorException($errorCode, []);
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
            case GroupUserOrder::PAYMENT_CHANNEL_YINLIAN:
                // 支付参数
                $options = [
                    'out_trade_no' => $outTradeNo, // 商户订单号
                    'total_amount' => $groupUserOrder->getTotal() * 100, // 订单金额，**单位：分**
                    'body'      => $body, // 支付订单描述
                    'spbill_create_ip'      => CommonUtil::getUserIp(), // IP
                    'sub_openid' => $user->getWxOpenId(),
                ];
                $result = FactoryUtil::yinlianPayDriver(Pay::APP_GATEWAY)->apply($options);
                $prePayInfo = isset($result['sign']['pay_info'])?$result['sign']['pay_info']:[];
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

                if (empty($prePayInfo['prepayid'])) {
                    $requestProcess->throwErrorException(ErrorCode::ERROR_WX_PAY_PREPAY_ID, []);
                }
                $prePayId = $prePayInfo['prepayid'];
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
     * 支付同步通知
     * @Route("/auth/groupUserOrder/notifyPayment", name="appNotifyGroupUserOrderPayment", methods="POST")
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
        ];

        //系统课报名处理
        $product = $groupUserOrder->getProduct();
        if ($product->isCourseProduct() && !$product->getCourse()->isOnline()) {
            $course = $product->getCourse();
            if ($course->isSystemSubject()) {
                if ($user->isSystemSubjectPrivilege()) {
                    //todo sms通知
                    $data['nextPageType'] = 4;
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
                if ($course->getPrice() == MoneyUtil::tradeSpecialPrice()) {
                    $data['nextPageType'] = 5;
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

        $logPath = ConfigParams::getParamWithController('kernel.project_dir'). '/var/log/';

        if (is_dir($logPath)) {
            file_put_contents($logPath .'test.pay.log', json_encode($data) . "\n", FILE_APPEND);
        }

        return FactoryUtil::notifyProcess(file_get_contents('php://input'))->process()->toResponse();
    }


    /**
     * 普通购买活动订单
     * @Route("/auth/pay/groupUserOrder/createOfflineCourse", name="appCreateOfflineCourseGroupUserOrder", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function createOfflineCourseAction(Request $request, ProductRepository $productRepository,GroupUserOrderRepository $groupUserOrderRepository)
    {
        $requestProcess = $this->processRequest($request, ['productId','payForOrderId'], ['productId']);

        $productId = $requestProcess['productId'];
        $user = $this->getAppUser();
        $product = $productRepository->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        $offlineCourseOrder = CourseOrder::factory($user, $product);

        // 返回金订单
        $payForOrderId = isset($requestProcess['payForOrderId'])?$requestProcess['payForOrderId']:null;
        if( $payForOrderId ){
            $offlineCourseOrder->setPayForOrderId($payForOrderId);
        }

        $this->entityPersist($offlineCourseOrder);

        return $requestProcess->toJsonResponse([
            'groupUserOrder' => $offlineCourseOrder->getArray()
        ]);
    }

    /**
     * 订单待支付页面
     *
     * @Route("/auth/pay/groupUserOrder/view", name="appViewGroupUserOrder", methods="POST")
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
            'needAdminConfirm' => false,
            'partnerName' => '',
        ];

        if (!empty($requestProcess['isConfirmView'])) {
            $data['hasConfirmPrivilege'] = $groupUserOrder->getUser()->getBianxianTopParentPartnerUpUser() == $user;
        } else {
            if (empty($groupUserOrder->getCheckStatus())) {
                if ($groupUserOrder->isNeedAdminConfirm()) {
                    $data['needAdminConfirm'] = true;
                }
                //是否需要确认
                if ($groupUserOrder->isNeedPartnerConfirm()) {
                    $data['needConfirm'] = true;
                    $data['partnerName'] = CommonUtil::getInsideValue($groupUserOrder, 'getUser.getBianxianTopParentPartnerUpUser.getName', '佐商学院');
                }
            }
        }

        return $requestProcess->toJsonResponse($data);
    }
}