<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-31
 * Time: 11:14 PM
 */

namespace App\Command\Notification;


use App\Command\SerializableCommandInterface;
use App\Entity\GroupUserOrder;

/**
 * 订单返现通知
 * 1. 拼团订单，通知团长，团员返现
 *
 * Class NotifyUserRewardsSentCommand
 * @package App\Command\Notification
 */
class NotifyUserRewardsSentCommand implements SerializableCommandInterface
{
    private $groupUserOrderId;

    /**
     * NotifyUserRewardsSentCommand constructor.
     * @param GroupUserOrder $groupUserOrder
     */
    public function __construct(GroupUserOrder $groupUserOrder)
    {

        $this->groupUserOrderId = $groupUserOrder->getId();
    }

    public function getGroupUserOrderId() {
        return $this->groupUserOrderId;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return '{"groupUserOrderId":' . $this->groupUserOrderId. ' }';
    }

    /**
     * @param $json
     * @return $this
     */
    public function deserialize($json)
    {
        return json_decode($json, true);
    }
}