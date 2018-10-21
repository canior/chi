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
use App\Repository\GroupOrderRepository;
use App\Service\Wx\WxCommon;
use Psr\Log\LoggerInterface;

class NotifyPendingGroupOrderCommandHandler // extends AbstractCommandHandler
{
    private $groupOrderRepository;
    private $log;

    /**
     * NotifyPendingGroupOrderCommandHandler constructor.
     * @param GroupOrderRepository $groupOrderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(GroupOrderRepository $groupOrderRepository, LoggerInterface $logger)
    {
        $this->groupOrderRepository = $groupOrderRepository;
        $this->log = $logger;
    }

    /**
     * @param CommandInterface|NotifyPendingGroupOrderCommand $command
     * @return mixed
     */
    public function handle(NotifyPendingGroupOrderCommand $command)
    {
        $groupOrderId = $command->getGroupOrderId();
        $groupOrder = $this->groupOrderRepository->find($groupOrderId);
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $formId = $groupUserOrder->getPrePayId();
        $templateId = "9RLVQOC7gP3qJipiFX2efKvz2oSYuJRs0dDaY2UeDIA";
        $page = "pages/group/index?id=" . $groupOrderId;
        $toUser = $groupUserOrder->getUser()->getWxOpenId();
        $data = [['keyword1' => ['value' => $groupOrder->getProduct()->getTitle()]],
            ['keyword2' => ['value' => $groupOrder->getProduct()->getPrice()]],
            ['keyword3' => ['value' => 1]],
            ['keyword4' => ['value' => $groupOrder->getExpiredAt(true)]]];
        $emphasisKeyword = "keyword3.DATA";

        $wxApi = new WxCommon($this->log);
        $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
    }
}