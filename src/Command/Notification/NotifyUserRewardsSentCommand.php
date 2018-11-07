<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-31
 * Time: 11:14 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

/**
 * 订单返现通知
 * 1. 拼团订单，通知团长，团员返现
 *
 * Class NotifyUserRewardsSentCommand
 * @package App\Command\Notification
 */
class NotifyUserRewardsSentCommand implements SerializableCommandInterface
{
    use SerializerTrait;

    private $groupUserOrderId;

    /**
     * NotifyUserRewardsSentCommand constructor.
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