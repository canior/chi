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

class NotifyPendingGroupOrderCommand implements SerializableCommandInterface
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