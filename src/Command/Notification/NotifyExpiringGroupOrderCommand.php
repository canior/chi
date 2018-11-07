<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-31
 * Time: 11:12 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

/**
 * 通知团长开团即将过期通知
 * Class NotifyExpiringGroupOrderCommand
 * @package App\Command\Notification
 */
class NotifyExpiringGroupOrderCommand implements SerializableCommandInterface
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