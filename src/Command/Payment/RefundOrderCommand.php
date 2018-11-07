<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-04
 * Time: 1:38 PM
 */

namespace App\Command\Payment;

use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

class RefundOrderCommand implements SerializableCommandInterface
{
    use SerializerTrait;

    private $groupUserOrderId;

    /**
     * RefundOrderCommand constructor.
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