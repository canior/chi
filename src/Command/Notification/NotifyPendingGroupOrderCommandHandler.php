<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-19
 * Time: 2:53 PM
 */

namespace App\Command\Notification;


use App\Command\AbstractCommandHandler;
use App\Command\CommandInterface;
use App\Entity\GroupOrder;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Service\Wx\WxCommon;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class NotifyPendingGroupOrderCommandHandler // extends AbstractCommandHandler
{
    private $entityManager;
    private $log;

    /**
     * NotifyPendingGroupOrderCommandHandler constructor.
     * @param ObjectManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->log = $logger;
    }

    /**
     * 拼团待成团提醒
     * 商品名称 {{keyword1.DATA}}
     * 商品价格 {{keyword2.DATA}}
     * 剩余人数 {{keyword3.DATA}}
     * 拼团结束时间 {{keyword4.DATA}}
     * @param CommandInterface|NotifyPendingGroupOrderCommand $command
     * @return mixed
     */
    public function handle(NotifyPendingGroupOrderCommand $command)
    {
        $groupOrderId = $command->getGroupOrderId();
        /**
         * @var GroupOrderRepository $groupOrderRepository
         */
        $groupOrderRepository = $this->entityManager->getRepository(GroupOrder::class);
        $groupOrder = $groupOrderRepository->find($groupOrderId);
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $formId = $groupUserOrder->getPrePayId();
        $templateId = "9RLVQOC7gP3qJipiFX2efKvz2oSYuJRs0dDaY2UeDIA";
        $page = "pages/group/index?id=" . $groupOrderId;
        $toUser = $groupUserOrder->getUser()->getWxOpenId();
        $data = [
            'keyword1' => ['value' => $groupOrder->getProduct()->getTitle()],
            'keyword2' => ['value' => $groupOrder->getProduct()->getPrice()],
            'keyword3' => ['value' => 1],
            'keyword4' => ['value' => $groupOrder->getExpiredAt(true)]
        ];
        $emphasisKeyword = "";

        $wxApi = new WxCommon($this->log);
        $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
    }
}