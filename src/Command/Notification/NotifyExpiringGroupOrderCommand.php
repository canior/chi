<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-31
 * Time: 11:12 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Entity\GroupOrder;

/**
 * 通知团长开团即将过期通知
 * Class NotifyExpiringGroupOrderCommand
 * @package App\Command\Notification
 */
class NotifyExpiringGroupOrderCommand implements SerializableCommandInterface
{
    private $groupOrderId;

    /**
     * NotifyPendingGroupOrderCommand constructor.
     * @param GroupOrder $groupOrder
     */
    function __construct(GroupOrder $groupOrder)
    {
        $this->groupOrderId = $groupOrder->getId();
    }

    /**
     * @return int
     */
    public function getGroupOrderId() : int {
        return $this->groupOrderId;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return '{"groupOrderId":' . $this-> groupOrderId. ' }';
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