<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-31
 * Time: 11:17 PM
 */

namespace App\Command\Notification;


use App\Command\SerializableCommandInterface;
use App\Entity\GroupUserOrder;

/**
 * 拼团/普通订单收益到账通知
 * Class NotifyOrderRewardsSentCommand
 * @package App\Command\Notification
 */
class NotifyOrderRewardsSentCommand implements SerializableCommandInterface
{
    private $groupUserOrderId;

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