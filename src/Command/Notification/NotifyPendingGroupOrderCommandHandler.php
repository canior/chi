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

class NotifyPendingGroupOrderCommandHandler  extends AbstractCommandHandler
{
    private $groupOrderRepository;

    /**
     * NotifyPendingGroupOrderCommandHandler constructor.
     * @param GroupOrderRepository $groupOrderRepository
     */
    public function __construct(GroupOrderRepository $groupOrderRepository)
    {
        $this->groupOrderRepository = $groupOrderRepository;
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
        
        $messageArray = [];

        //TODO sendMessage

    }
}