<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-19
 * Time: 3:04 PM
 */

namespace App\Command\Notification;


use App\Command\SerializableCommandInterface;

class NotifyCompletedGroupOrderCommand implements SerializableCommandInterface
{
    private $groupOrderId;

    /**
     * NotifyPendingGroupOrderCommand constructor.
     * @param $groupOrderId
     */
    function __construct($groupOrderId)
    {
        $this->groupOrderId = $groupOrderId;
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
        // TODO: Implement serialize() method.
    }

    /**
     * @param $json
     * @return $this
     */
    public function deserialize($json)
    {
        // TODO: Implement deserialize() method.
    }
}