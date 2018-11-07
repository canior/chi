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
 * 通知团长拼团过期通知, 并退款
 *
 * Class NotifyExpiredGroupOrderCommand
 * @package App\Command\Notification
 */
class NotifyExpiredGroupOrderCommand implements SerializableCommandInterface
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