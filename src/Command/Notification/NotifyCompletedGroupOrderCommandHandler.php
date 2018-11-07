<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-19
 * Time: 3:05 PM
 */

namespace App\Command\Notification;

use App\Command\CommandInterface;
use App\Entity\GroupOrder;
use App\Repository\GroupOrderRepository;
use App\Service\Wx\WxCommon;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class NotifyCompletedGroupOrderCommandHandler
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
     * NotifyCompletedGroupOrderCommandHandler constructor.
     * @param ObjectManager $entityManager
     * @param LoggerInterface $log
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $log)
    {
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    /**
     * 拼团成功通知
     * 商品名称 {{keyword1.DATA}}
     * 商品金额 {{keyword2.DATA}}
     * 拼团价 {{keyword3.DATA}}
     * 成团时间 {{keyword4.DATA}}
     * 开团时间 {{keyword5.DATA}}
     * 订单号 {{keyword6.DATA}}
     * 订单状态 {{keyword7.DATA}}
     * @param CommandInterface|NotifyCompletedGroupOrderCommand $command
     * @return mixed
     */
    public function handle(NotifyCompletedGroupOrderCommand $command)
    {
        $groupOrderId = $command->getGroupOrderId();
        /**
         * @var GroupOrderRepository $groupOrderRepository
         */
        $groupOrderRepository = $this->entityManager->getRepository(GroupOrder::class);
        $groupOrder = $groupOrderRepository->find($groupOrderId);
        foreach ($groupOrder->getGroupUserOrders() as $groupUserOrder) {
            $formId = $groupUserOrder->getPrePayId();
            $templateId = "97wd2Pa-NqRd8jHh0zNEOfZQI_btsAr3vpYu7SvVRQo";
            $page = "pages/group/index?id=" . $groupOrderId;
            $toUser = $groupUserOrder->getUser()->getWxOpenId();
            $data = [
                'keyword1' => ['value' => $groupOrder->getProduct()->getTitle()],
                'keyword2' => ['value' => $groupOrder->getProduct()->getOriginalPrice()],
                'keyword3' => ['value' => $groupOrder->getProduct()->getGroupPrice()],
                'keyword4' => ['value' => $groupUserOrder->getCreatedAt()],
                'keyword5' => ['value' => $groupOrder->getCreatedAt()],
                'keyword6' => ['value' => $groupUserOrder->getId()],
                'keyword7' => ['value' => $groupUserOrder->getStatusText()]
            ];
            $emphasisKeyword = "";

            $wxApi = new WxCommon($this->log);
            $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
        }
    }
}