<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-11-07
 * Time: 16:29
 */

namespace App\Command\Notification;

use App\Command\CommandInterface;
use App\Entity\GroupOrder;
use App\Repository\GroupOrderRepository;
use App\Service\Wx\WxCommon;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class NotifyExpiringGroupOrderCommandHandler
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
     * NotifyExpiringGroupOrderCommandHandler constructor.
     * @param ObjectManager $entityManager
     * @param LoggerInterface $log
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $log)
    {
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    /**
     * 通知团长开团即将过期通知
     * 商品名称 {{keyword1.DATA}}
     * 订单金额 {{keyword2.DATA}}
     * 过期时间 {{keyword3.DATA}}
     * 温馨提示 {{keyword4.DATA}}
     * @param CommandInterface|NotifyExpiringGroupOrderCommand $command
     * @return mixed
     */
    public function handle(NotifyExpiringGroupOrderCommand $command)
    {
        $groupOrderId = $command->getGroupOrderId();
        /**
         * @var GroupOrderRepository $groupOrderRepository
         */
        $groupOrderRepository = $this->entityManager->getRepository(GroupOrder::class);
        $groupOrder = $groupOrderRepository->find($groupOrderId);
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $formId = $groupUserOrder->getPrePayId();
        $templateId = "e3Up2LoLjskeL1gHIp3EQhTBGQ-qkm7qLfWWm0LzeTI";
        $page = "pages/group/index?id=" . $groupOrderId;
        $toUser = $groupUserOrder->getUser()->getWxOpenId();
        $data = [['keyword1' => ['value' => $groupOrder->getProduct()->getTitle()]],
            ['keyword2' => ['value' => $groupUserOrder->getTotal()]],
            ['keyword3' => ['value' => $groupOrder->getExpiredAt()]],
            ['keyword4' => ['value' => '您发起的拼团即将过期，快找人来帮忙拼团吧!']]];
        $emphasisKeyword = "keyword4.DATA";

        $wxApi = new WxCommon($this->log);
        $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
    }
}