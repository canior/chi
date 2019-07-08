<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 12:19
 */

namespace App\Service\Pay\Notify;


use App\Entity\CommandMessage;
use App\Entity\CourseOrder;
use App\Entity\GroupUserOrder;
use App\Entity\MessageGroupUserOrderMeta;
use App\Entity\User;
use App\Repository\GroupUserOrderRepository;
use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\Order\OfflineTableNo;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use App\Service\Util\MoneyUtil;

trait NotifyProcessTrait
{
    /**
     * @param string $outTradeNo
     * @author zxqc2018
     * @return \App\Service\ResultData
     */
    public function processOrder(string $outTradeNo)
    {
        $requestProcess = CommonUtil::resultData();
        $groupUserOrder = $this->getOrderInfo($outTradeNo);

        if (empty($groupUserOrder)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS, []);
        }

        if ($groupUserOrder->isPaid()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_ORDER_ALREADY_PAY, []);
        }

        $user = $groupUserOrder->getUser();

        $groupUserOrder->setPending();

        if ($groupUserOrder instanceof  CourseOrder) {
            $groupUserOrder->setRegistered();
        } else {
            $groupUserOrder->setPaid();
        }

        CommonUtil::entityPersist($groupUserOrder);

        if ($groupUserOrder->getProduct()->isHasCoupon()) {
            $user->addUserCommand(CommandMessage::createNotifyCompletedCouponProductCommand($groupUserOrder->getId()));
        }

        $groupUserOrder->setPaymentTime(time());

        $data = [];

        $isFlushGroupUserOrder = false;
        //系统课报名处理
        $product = $groupUserOrder->getProduct();
        if ($product->isCourseProduct() && !$product->getCourse()->isOnline()) {
            $course = $product->getCourse();
            if ($course->isSystemSubject()) {
                if ($user->isSystemSubjectPrivilege(false)) {
                    $groupUserOrder->setTableNo((int)OfflineTableNo::getUserTable($groupUserOrder));
                    $groupUserOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                    $groupUserOrder->setCheckAt(time());
                    //todo sms通知
                    $data['nextPageType'] = 4;
                } else {
                    $data['nextPageType'] = 3;
                }
            } else if ($course->isThinkingSubject()) {
                if ($course->getPrice() > MoneyUtil::thinkingGeneratePrice()) {
                    $groupUserOrder->setTableNo((int)OfflineTableNo::getUserTable($groupUserOrder));
                    $data['nextPageType'] = 2;
                } else {
                    //todo sms通知
                    $data['nextPageType'] = 1;
                }
                $groupUserOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                $groupUserOrder->setCheckAt(time());
            } else if ($course->isPrivateDirectSubject()) {
                $data['nextPageType'] = 1;
                $groupUserOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                $groupUserOrder->setCheckAt(time());
            } else if ($course->isTradingSubject()) {
                $groupUserOrder->setTableNo((int)OfflineTableNo::getUserTable($groupUserOrder));
                $groupUserOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                $groupUserOrder->setCheckAt(time());
                CommonUtil::entityPersist($groupUserOrder);
                $isFlushGroupUserOrder = true;
                //有上一级则发送消息
                if (!empty($groupUserOrder->getUser()->getBianxianTopParentPartnerUpUser())) {
                    $message = new MessageGroupUserOrderMeta();
                    $name = CommonUtil::getInsideValue($groupUserOrder, 'getUser.getName', '');
                    $phone = CommonUtil::getInsideValue($groupUserOrder, 'getUser.getPhone', '');
                    $message->setDataId($groupUserOrder->getId());
                    $message->setUser($groupUserOrder->getUser()->getBianxianTopParentPartnerUpUser());
                    $message->setContent("您的用户{$name},手机号为{$phone}完成了系统学员的身份升级，您的名额将扣除一个。");
                    $message->setTitle("学员升级");
                    CommonUtil::entityPersist($message);
                }
            }
        }

        if (!$isFlushGroupUserOrder) {
            CommonUtil::entityPersist($groupUserOrder);
        }
        return $requestProcess;
    }

    /**
     * @param string $outTradeNo
     * @return GroupUserOrder|null
     * @author zxqc2018
     */
    public function getOrderInfo(string $outTradeNo)
    {
        /**
         * @var GroupUserOrderRepository $groupUserOrderRepository
         */
        $groupUserOrderRepository = ConfigParams::getRepositoryManager()->getRepository(GroupUserOrder::class);
        $groupUserOrder = $groupUserOrderRepository->findOneBy(['outTradeNo' => $outTradeNo]);

        return $groupUserOrder;
    }
}