<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-03-17
 * Time: 6:55 PM
 */

namespace App\Command\Notification;

use App\Command\CommandInterface;
use App\Entity\GroupOrder;
use App\Entity\UpgradeOrderCoupon;
use App\Repository\GroupUserOrderRepository;
use App\Repository\UpgradeOrderCouponRepository;
use App\Service\Wx\WxCommon;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use App\Entity\GroupUserOrder;

class NotifyCompletedCouponProductCommandHandler
{
    /**
     * @var ObjectManager $entityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param ObjectManager $entityManager
     * @param LoggerInterface $log
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $log)
    {
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    /**
     * 升级码下单成功通知
     * 交易单号 {{keyword1.DATA}}
     * 物品名称 {{keyword2.DATA}}
     * 购买时间 {{keyword3.DATA}}
     * 备注 {{keyword4.DATA}}
     *
     * @param NotifyCompletedCouponProductCommand $command
     * @return mixed
     */
    public function handle(NotifyCompletedCouponProductCommand $command)
    {
        $groupUserOrderId = $command->getGroupUserOrderId();

        /**
         * @var GroupUserOrderRepository $groupUserOrderRepository
         */
        $groupUserOrderRepository = $this->entityManager->getRepository(GroupUserOrder::class);
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        /**
         * @var UpgradeOrderCouponRepository $upgradeOrderCouponRepository
         */
        $upgradeOrderCouponRepository = $this->entityManager->getRepository(UpgradeOrderCoupon::class);
        $coupons = $upgradeOrderCouponRepository->createCoupons(6);
        $couponsString = "";
        for ($i = 0; $i < sizeof($coupons); $i++) {
            $coupon = $coupons[$i];
            if ($i === 0 and $groupUserOrder->getUpgradeUserOrder()->getOldUserLevel() != $groupUserOrder->getUpgradeUserOrder()->getUserLevel()) {
                $upgradeOrderCoupon = UpgradeOrderCoupon::factory($groupUserOrder, $coupon, $groupUserOrder->getUpgradeUserOrder(), $groupUserOrder->getUser());
                $couponsString .=  $coupon . "(已用)\n";
            } else {
                $upgradeOrderCoupon = UpgradeOrderCoupon::factory($groupUserOrder, $coupon);
                $couponsString .=  $coupon . "\n";
            }
            $this->entityManager->persist($upgradeOrderCoupon);
        }
        $this->entityManager->flush();

        $formId = $groupUserOrder->getPrePayId();
        $templateId = "UuD9-sOSncBNKiV1VXhD2xoBu0cIYhEherxMm9N1cIE";
        $page = "pages/user/order/detail?id=" . $groupUserOrderId;
        $toUser = $groupUserOrder->getUser()->getWxOpenId();

        $data = [
            'keyword1' => ['value' => $groupUserOrderId],
            'keyword2' => ['value' => $groupUserOrder->getProduct()->getTitle()],
            'keyword3' => ['value' => $groupUserOrder->getCreatedAt(true)],
            'keyword4' => ['value' => "特级VIP升级码: \n" . $couponsString],
        ];
        $emphasisKeyword = "";

        $wxApi = new WxCommon($this->log);
        $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
    }
}
