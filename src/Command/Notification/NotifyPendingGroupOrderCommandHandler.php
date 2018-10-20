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

class NotifyPendingGroupOrderCommandHandler  extends AbstractCommandHandler
{
    private $groupOrderRepository;

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
    public function handle(CommandInterface $command)
    {
        $groupOrderId = $command->getGroupOrderId();
        $groupOrder = $this->groupOrderRepository->find($groupOrderId);
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $formId = $groupUserOrder->getPrePayId();
        $templateId = "";
        $page = "";
        $toUser = $groupUserOrder->getUser()->getWxOpenId();
        $data = [];
        $emphasisKeyword = "";

        $wxApi = new WxCommon($this->log);
        $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);

        //TODO 这里要判断如果发送失败怎么办
    }
}