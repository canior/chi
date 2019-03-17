<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-03-17
 * Time: 6:53 PM
 */

namespace App\Command\Notification;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

class NotifyCompletedCouponProductCommand implements SerializableCommandInterface
{
    use SerializerTrait;

    /**
     * @var int $groupUserOrderId
     */
    private $groupUserOrderId;

    /**
     * NotifyCompletedCouponProductCommand constructor.
     * @param $groupUserOrderId
     */
    public function __construct($groupUserOrderId) {
        $this->groupUserOrderId = $groupUserOrderId;
    }

    /**
     * @return int
     */
    public function getGroupUserOrderId(): int
    {
        return $this->groupUserOrderId;
    }

    /**
     * @param int $groupUserOrderId
     */
    public function setGroupUserOrderId(int $groupUserOrderId): void
    {
        $this->groupUserOrderId = $groupUserOrderId;
    }


}