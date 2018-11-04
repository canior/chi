<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-04
 * Time: 1:39 PM
 */

namespace App\Command\Payment;


use App\Entity\GroupUserOrder;

class SendUserRewardsCommand
{
    private $groupUserOrderId;

    /**
     * SendUserRewardsCommand constructor.
     * @param GroupUserOrder $groupUserOrder
     */
    public function __construct(GroupUserOrder $groupUserOrder)
    {

        $this->groupUserOrderId = $groupUserOrder->getId();
    }

    public function getGroupUserOrderId() {
        return $this->groupUserOrderId;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return '{"groupUserOrderId":' . $this->groupUserOrderId. ' }';
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