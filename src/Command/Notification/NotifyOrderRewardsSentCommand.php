<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-31
 * Time: 11:17 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

/**
 * 拼团/普通订单收益到账通知
 * Class NotifyOrderRewardsSentCommand
 * @package App\Command\Notification
 */
class NotifyOrderRewardsSentCommand implements SerializableCommandInterface
{
    use SerializerTrait;

    private $groupUserOrderId;

    /**
     * NotifyOrderRewardsSentCommand constructor.
     * @param int $groupUserOrderId
     */
    public function __construct($groupUserOrderId)
    {
        $this->groupUserOrderId = $groupUserOrderId;
    }

    public function getGroupUserOrderId()
    {
        return $this->groupUserOrderId;
    }
}