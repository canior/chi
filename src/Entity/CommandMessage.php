<?php

namespace App\Entity;

use App\Command\Notification\NotifyCompletedGroupOrderCommand;
use App\Command\Notification\NotifyExpiredGroupOrderCommand;
use App\Command\Notification\NotifyExpiringGroupOrderCommand;
use App\Command\Notification\NotifyOrderRewardsSentCommand;
use App\Command\Notification\NotifyPendingGroupOrderCommand;
use App\Command\Notification\NotifyUserRewardsSentCommand;
use App\Command\Payment\RefundOrderCommand;
use App\Command\Payment\SendGroupUserOrderRewardCommand;
use App\Command\Payment\SendOrderRewardCommand;
use App\Command\Payment\SendOrderRewardsCommand;
use App\Command\Payment\SendUserRewardsCommand;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandMessageRepository")
 */
class CommandMessage implements Dao
{
    const PENDING = 'PENDING';
    const PROCESSING = 'PROCESSING';
    const COMPLETED = 'COMPLETED';
    const ERROR = 'ERROR';

    use IdTrait,
        StatusTrait,
        CreatedAtTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commandClass;

    /**
     * @ORM\Column(type="text")
     */
    private $commandData;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $runAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $completedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $multithread;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userCommands")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * CommandMessage constructor.
     */
    public function __construct()
    {
        $this->setStatus(self::PENDING);
        $this->setCreatedAt(time());
        $this->setMultithread(0);
    }

    public function setUser(?User $user) {
        $this->user = $user;
        return $this;
    }

    public function getUser() {
        return $this->user;
    }

    public function getCommandClass(): ?string
    {
        return $this->commandClass;
    }

    public function setCommandClass(string $commandClass): self
    {
        $this->commandClass = $commandClass;

        return $this;
    }

    public function getCommandData(): ?string
    {
        return $this->commandData;
    }

    public function setCommandData(string $commandData): self
    {
        $this->commandData = $commandData;

        return $this;
    }

    public function getRunAt(): ?int
    {
        return $this->runAt;
    }

    public function setRunAt(?int $runAt): self
    {
        $this->runAt = $runAt;

        return $this;
    }

    public function getCompletedAt(): ?int
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?int $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getMultithread(): ?int
    {
        return $this->multithread;
    }

    public function setMultithread(int $multithread): self
    {
        $this->multithread = $multithread;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultithread() {
        return $this->multithread > 0;
    }

    public function setError() {
        $this->status = self::ERROR;
        $this->setCompletedAt(time());
    }

    public function setProcessing() {
        $this->status = self::PROCESSING;
        $this->setRunAt(time());
    }

    public function setCompleted() {
        $this->status = self::COMPLETED;
        $this->setCompletedAt(time());
    }

    /**
     * 生成发放订单返现队列CommandMessage
     * @param GroupUserOrder $groupUserOrder
     * @return CommandMessage
     */
    public static function createSendOrderRewardsCommand(GroupUserOrder $groupUserOrder) {
        $qCommand = new SendOrderRewardsCommand($groupUserOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成发放传销返现队列CommandMessage
     * @param GroupUserOrder $groupUserOrder
     * @return CommandMessage
     */
    public static function createSendUserRewardsCommand(GroupUserOrder $groupUserOrder) {
        $qCommand = new SendUserRewardsCommand($groupUserOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成发放订单退款队列CommandMessage
     * @param GroupUserOrder $groupUserOrder
     * @return CommandMessage
     */
    public static function createRefundOrderCommand(GroupUserOrder $groupUserOrder) {
        $qCommand = new RefundOrderCommand($groupUserOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成拼团订单完成通知CommandMessage
     * @param GroupOrder $groupOrder
     * @return CommandMessage
     */
    public static function createNotifyCompletedGroupOrderCommand(GroupOrder $groupOrder) {
        $qCommand = new NotifyCompletedGroupOrderCommand($groupOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成拼团即将过期通知CommandMessage
     * @param GroupOrder $groupOrder
     * @return CommandMessage
     */
    public static function createNotifyExpiringGroupOrderCommand(GroupOrder $groupOrder) {
        $qCommand = new NotifyExpiringGroupOrderCommand($groupOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成拼团已经过期通知CommandMessage
     * @param GroupOrder $groupOrder
     * @return CommandMessage
     */
    public static function createNotifyExpiredGroupOrderCommand(GroupOrder $groupOrder) {
        $qCommand = new NotifyExpiredGroupOrderCommand($groupOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成订单返现通知CommandMessage
     * @param GroupUserOrder $groupUserOrder
     * @return CommandMessage
     */
    public static function createNotifyOrderRewardsSentCommand(GroupUserOrder $groupUserOrder) {
        $qCommand = new NotifyOrderRewardsSentCommand($groupUserOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成开团成功通知CommandMessage
     * @param GroupOrder $groupOrder
     * @return CommandMessage
     */
    public static function createNotifyPendingGroupOrderCommand(GroupOrder $groupOrder) {
        $qCommand = new NotifyPendingGroupOrderCommand($groupOrder->getId());
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }

    /**
     * 生成用户传销返现通知CommandMessage
     * @param GroupUserOrder $groupUserOrder
     * @return CommandMessage
     */
    public static function createNotifyUserRewardsSentCommand(GroupUserOrder $groupUserOrder) {
        $qCommand = new NotifyUserRewardsSentCommand($groupUserOrder);
        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread(true);
        $commandMessage->setCommandClass(get_class($qCommand));
        $commandMessage->setCommandData($qCommand->serialize());
        return $commandMessage;
    }
}
