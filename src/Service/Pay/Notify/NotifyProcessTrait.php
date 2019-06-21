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
use App\Entity\User;
use App\Repository\GroupUserOrderRepository;
use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\Order\OfflineTableNo;
use App\Service\Util\CommonUtil;

trait NotifyProcessTrait
{
    /**
     * 补发用户的桌号
     * @param User $user
     * @return bool
     * @author zxqc2018
     */
    protected function supplySystemTableNo(User $user)
    {
        $res = false;

        if ($user->isSystemSubjectPrivilege()) {
            /**
             * @var GroupUserOrderRepository $groupUserOrderRepository
             */
            $groupUserOrderRepository = ConfigParams::getRepositoryManager()->getRepository(GroupUserOrder::class);
            //是否有报名了但是没有分配桌号的
            $notDistributeOrders = $groupUserOrderRepository->findBy(['user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
            if (!empty($notDistributeOrders)) {
                foreach ($notDistributeOrders as $notDistributeOrder) {
                    if (!empty($notDistributeOrder->getPaymentTime()) && $notDistributeOrder->getProduct()->isCourseProduct() &&
                        !$notDistributeOrder->getProduct()->getCourse()->isOnline() && $notDistributeOrder->getProduct()->getCourse()->isSystemSubject() &&
                        empty($notDistributeOrder->getTableNo())) {
                        $notDistributeOrder->setTableNo((int)$this->getUserTable($notDistributeOrder));
                        $notDistributeOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                        $notDistributeOrder->setCheckAt(time());
                        CommonUtil::entityPersist($notDistributeOrder);
                        //todo sms通知
                        $res = true;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * @param string $outTradeNo
     * @author zxqc2018
     * @return \App\Service\ResultData
     */
    public function processOrder(string $outTradeNo)
    {
        $requestProcess = CommonUtil::resultData();
        $groupUserOrder = $this->getOrderInfo($outTradeNo);

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
                if ($user->isSystemSubjectPrivilege()) {
                    $groupUserOrder->setTableNo((int)OfflineTableNo::getUserTable($groupUserOrder));
                    $groupUserOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                    $groupUserOrder->setCheckAt(time());
                    //todo sms通知
                    $data['nextPageType'] = 4;
                } else {
                    $data['nextPageType'] = 3;
                }
            } else if ($course->isThinkingSubject()) {
                if ($course->getPrice() > 1) {
                    $groupUserOrder->setTableNo((int)OfflineTableNo::getUserTable($groupUserOrder));
                    $data['nextPageType'] = 2;
                } else {
                    //todo sms通知
                    $data['nextPageType'] = 1;
                }
            } else if ($course->isTradingSubject() && $user->isCompletedPersonalInfo()) {
                CommonUtil::entityPersist($groupUserOrder);
                $isFlushGroupUserOrder = true;
                //是否有报名了但是没有分配桌号的
                $isSupplyTableNo = $this->supplySystemTableNo($groupUserOrder->getUser());
                if ($isSupplyTableNo && $groupUserOrder->getTotal() == 12000) {
                    $data['nextPageType'] = 4;
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