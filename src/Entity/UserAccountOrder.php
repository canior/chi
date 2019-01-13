<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAccountOrder
 *
 * @ORM\Table(name="user_account_order")
 * @ORM\Entity
 */
class UserAccountOrder
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="user_account_order_type", type="string", length=50, nullable=true)
     */
    private $userAccountOrderType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="user_upgrade_order_rewards_id", type="integer", nullable=true)
     */
    private $userUpgradeOrderRewardsId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="payment_status", type="string", length=50, nullable=true)
     */
    private $paymentStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $createdAt;

    /**
     * @var int
     *
     * @ORM\Column(name="updated_at", type="integer", nullable=false)
     */
    private $updatedAt;


}
