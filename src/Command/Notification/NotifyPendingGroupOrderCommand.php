<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-19
 * Time: 2:53 PM
 */
namespace App\Command\Notification;

use App\Command\CommandInterface;
use App\Command\SerializableCommandInterface;
use App\Entity\GroupOrder;

/**
 * 团长开团通知
 * Class NotifyPendingGroupOrderCommand
 * @package App\Command\Notification
 */
class NotifyPendingGroupOrderCommand implements SerializableCommandInterface
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