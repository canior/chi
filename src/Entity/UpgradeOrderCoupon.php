<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-03-17
 * Time: 6:44 PM
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\CreatedAtTrait;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UpgradeOrderCouponRepository")
 */
class UpgradeOrderCoupon implements Dao
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $coupon;

    /**
     * @var GroupUserOrder $groupUserOrder
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupUserOrder")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupUserOrder;

    /**
     * @var User|null $upgradeUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $upgradeUser;


    /**
     * @var UpgradeUserOrder|null $upgradeUserOrder
     * @ORM\ManyToOne(targetEntity="App\Entity\UpgradeUserOrder")
     * @ORM\JoinColumn(nullable=true)
     */
    private $upgradeUserOrder;

    /**
     * @param GroupUserOrder $groupUserOrder
     * @param $coupon
     * @param UpgradeUserOrder|null $upgradeUserOrder
     * @param User $upgradeUser
     * @return UpgradeOrderCoupon
     */
    public static function factory(GroupUserOrder $groupUserOrder, $coupon, UpgradeUserOrder $upgradeUserOrder = null, User $upgradeUser = null) {
        $upgradeOrderCoupon = new UpgradeOrderCoupon();
        $upgradeOrderCoupon->setGroupUserOrder($groupUserOrder);
        $upgradeOrderCoupon->setCoupon($coupon);
        $upgradeOrderCoupon->setUpgradeUserOrder($upgradeUserOrder);
        $upgradeOrderCoupon->setUpgradeUser($upgradeUser);

        return $upgradeOrderCoupon;
    }

    public function __construct()
    {
        $this->setCreatedAt();
    }

    /**
     * @return string
     */
    public function getCoupon(): string
    {
        return $this->coupon;
    }

    /**
     * @param string $coupon
     */
    public function setCoupon(string $coupon): void
    {
        $this->coupon = $coupon;
    }

    /**
     * @return GroupUserOrder
     */
    public function getGroupUserOrder(): GroupUserOrder
    {
        return $this->groupUserOrder;
    }

    /**
     * @param GroupUserOrder $groupUserOrder
     */
    public function setGroupUserOrder(GroupUserOrder $groupUserOrder): void
    {
        $this->groupUserOrder = $groupUserOrder;
    }

    /**
     * @return UpgradeUserOrder|null
     */
    public function getUpgradeUserOrder(): ?UpgradeUserOrder
    {
        return $this->upgradeUserOrder;
    }

    /**
     * @param UpgradeUserOrder|null $upgradeUserOrder
     */
    public function setUpgradeUserOrder(?UpgradeUserOrder $upgradeUserOrder): void
    {
        $this->upgradeUserOrder = $upgradeUserOrder;
    }

    /**
     * @return User|null
     */
    public function getUpgradeUser(): ?User
    {
        return $this->upgradeUser;
    }

    /**
     * @param User|null $upgradeUser
     */
    public function setUpgradeUser(?User $upgradeUser): void
    {
        $this->upgradeUser = $upgradeUser;
    }


}