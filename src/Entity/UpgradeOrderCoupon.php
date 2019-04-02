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
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupUserOrder", inversedBy="upgradeCoupons")
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
     * @var UpgradeUserOrder[] | ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\UpgradeUserOrder", mappedBy="upgradeOrderCoupon", cascade={"persist"}, orphanRemoval=true)
     */
    private $usedForUpgradeUserOrders;


    /**
     * @param GroupUserOrder $groupUserOrder
     * @param $coupon
     * @param User $upgradeUser
     * @return UpgradeOrderCoupon
     */
    public static function factory(GroupUserOrder $groupUserOrder, $coupon, User $upgradeUser = null) {
        $upgradeOrderCoupon = new UpgradeOrderCoupon();
        $upgradeOrderCoupon->setGroupUserOrder($groupUserOrder);
        $upgradeOrderCoupon->setCoupon($coupon);
        $upgradeOrderCoupon->setUpgradeUser($upgradeUser);

        return $upgradeOrderCoupon;
    }

    public function __construct()
    {
        $this->usedForUpgradeUserOrders = new ArrayCollection();
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

    /**
     * @param User $user
     */
    public function setApproved(User $user) {
        $this->setUpgradeUser($user);
        $recommander = $user->getParentUser();

        if ($recommander == null or time() > $recommander->getParentUserExpiresAt()) {
            $user->setParentUser($this->getGroupUserOrder()->getUser());
            $user->setParentUserExpiresAt(time() + User::PARENT_365_DAYS_EXPIRES_SECONDS);
        }

        $jinqiuUpgradeUserOrder = UpgradeUserOrder::factory(UpgradeUserOrder::JINQIU, $user, UserLevel::ADVANCED2, null);
        $jinqiuUpgradeUserOrder->setApproved(true, $this);
        $jinqiuUpgradeUserOrder->setUpgradeOrderCoupon($this);
        $this->addUsedUpgradeUserOrders($jinqiuUpgradeUserOrder);

        $bianxianUpgradeUserOrder = UpgradeUserOrder::factory(UpgradeUserOrder::BIANXIAN, $user, BianxianUserLevel::THINKING, null);
        $bianxianUpgradeUserOrder->setApproved(false, $this);
        $bianxianUpgradeUserOrder->setUpgradeOrderCoupon($this);
        $this->addUsedUpgradeUserOrders($bianxianUpgradeUserOrder);
    }

    /**
     * @return UpgradeUserOrder[]|ArrayCollection
     */
    public function getUsedForUpgradeUserOrders()
    {
        return $this->usedForUpgradeUserOrders;
    }

    /**
     * @param UpgradeUserOrder[]|ArrayCollection $usedForUpgradeUserOrders
     */
    public function setUsedForUpgradeUserOrders($usedForUpgradeUserOrders): void
    {
        $this->usedForUpgradeUserOrders = $usedForUpgradeUserOrders;
    }

    /**
     * @param UpgradeUserOrder $upgradeUserOrder
     */
    public function addUsedUpgradeUserOrders(UpgradeUserOrder $upgradeUserOrder) {
        if (!$this->usedForUpgradeUserOrders->contains($upgradeUserOrder))
            $this->usedForUpgradeUserOrders->add($upgradeUserOrder);
    }

    /**
     * @return array
     */
    public function getArray() {
        return [
            'coupon' => $this->coupon,
        ];
    }

}
