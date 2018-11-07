<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-19
 * Time: 3:04 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

/**
 * 通知团长，团员拼团成功
 * Class NotifyCompletedGroupOrderCommand
 * @package App\Command\Notification
 */
class NotifyCompletedGroupOrderCommand implements SerializableCommandInterface
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