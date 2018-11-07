<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-19
 * Time: 2:53 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

/**
 * 团长开团通知
 * Class NotifyPendingGroupOrderCommand
 * @package App\Command\Notification
 */
class NotifyPendingGroupOrderCommand implements SerializableCommandInterface
{
    use SerializerTrait;

    private $groupOrderId;

    /**
     * NotifyPendingGroupOrderCommand constructor.
     * @param int $groupOrderId
     */
    function __construct($groupOrderId)
    {
        $this->groupOrderId = $groupOrderId;
    }

    /**
     * @return int
     */
    public function getGroupOrderId(): int
    {
        return $this->groupOrderId;
    }
}