<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-04
 * Time: 1:39 PM
 */

namespace App\Command\Payment;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

class SendUserRewardsCommand implements SerializableCommandInterface
{
    use SerializerTrait;

    private $groupUserOrderId;

    /**
     * SendUserRewardsCommand constructor.
     * @param int $groupUserOrderId
     */
    public function __construct($groupUserOrderId)
    {
        $this->groupUserOrderId = $groupUserOrderId;
    }

    public function getGroupUserOrderId()
    {
        return $this->groupUserOrderId;
    }
}