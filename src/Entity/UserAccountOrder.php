<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserAccountOrder
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserAccountOrderRepository")
 */
class UserAccountOrder implements Dao
{
    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    const PAID = 'paid';
    const UNPAID = 'unpaid';

    public static $paymentStatuses = [
        self::PAID => '已支付',
        self::UNPAID => '未支付',
    ];

    const OLD_TEACHER_REWARDS = 'old_teacher_rewards';
    const TEACHER_REWARDS = 'teacher_rewards';
    const RECOMMAND_REWARDS = 'recommand_rewards';
    const WITHDRAW = 'withdraw';

    public static $userAccountOrderTypes = [
        self::OLD_TEACHER_REWARDS => '间接讲师佣金',
        self::TEACHER_REWARDS => '直接讲师佣金',
        self::RECOMMAND_REWARDS => '推荐佣金',
        self::WITHDRAW => '提现',
    ];

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userAccountOrders", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="user_account_order_type", type="string", length=50, nullable=false)
     */
    private $userAccountOrderType;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var UpgradeUserOrder
     * @ORM\ManyToOne(targetEntity="App\Entity\UpgradeUserOrder")
     */
    private $upgradeUserOrder;


    /**
     * @var string
     *
     * @ORM\Column(name="payment_status", type="string", length=50, nullable=false)
     */
    private $paymentStatus;

    /**
     * UserAccountOrder constructor.
     * @param User $user
     * @param string $userAccountOrderType
     * @param float $amount
     * @param UpgradeUserOrder|null $upgradeUserOrder
     */
    public function __construct(User $user, $userAccountOrderType, $amount, UpgradeUserOrder $upgradeUserOrder = null)
    {
        $this->setUser($user);
        $this->setUserAccountOrderType($userAccountOrderType);
        $this->setAmount($amount);
        $this->setPaymentStatus(self::UNPAID);

        $this->setCreatedAt();
        $this->setUpdatedAt();
        $this->setUpgradeUserOrder($upgradeUserOrder);
    }

    /**
     * @return bool
     */
    public function isWithdraw() {
        return $this->getUserAccountOrderType() == self::WITHDRAW;
    }

    /**
     * @return bool
     */
    public function isRecommandRewards() {
        return $this->getUserAccountOrderType() == self::RECOMMAND_REWARDS;
    }

    /**
     * @return bool
     */
    public function isTeacherRewards() {
        return $this->getUserAccountOrderType() == self::TEACHER_REWARDS;
    }

    /**
     * @return bool
     */
    public function isOldTeacherRewards() {
        return $this->getUserAccountOrderType() == self::OLD_TEACHER_REWARDS;
    }

    public function isPaid() {
        return $this->getPaymentStatus() == self::PAID;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUserAccountOrderType(): string
    {
        return $this->userAccountOrderType;
    }

    /**
     * @param string $userAccountOrderType
     */
    public function setUserAccountOrderType(string $userAccountOrderType): void
    {
        $this->userAccountOrderType = $userAccountOrderType;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus(string $paymentStatus): void
    {
        $this->paymentStatus = $paymentStatus;
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
    public function setUpgradeUserOrder(UpgradeUserOrder $upgradeUserOrder = null): void
    {
        $this->upgradeUserOrder = $upgradeUserOrder;
    }

    public function setPaid() {
        $this->paymentStatus = self::PAID;

        if ($this->isWithdraw()) {
            $this->getUser()->increaseUserAccountTotal(-$this->getAmount());
        } else {
            $this->getUser()->increaseUserAccountTotal($this->getAmount());
        }
    }

    /**
     * @return array
     */
    public function getArray() {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->getArray(),
            'type' => self::$userAccountOrderTypes[$this->getUserAccountOrderType()],
            'amount' => $this->getAmount(),
            'paymentStatus' => self::$paymentStatuses[$this->getPaymentStatus()],
            'createdAt' => $this->getCreatedAt(true),
            'upgradeUserOrder' => $this->getUpgradeUserOrder() ? $this->getUpgradeUserOrder()->getArray() : null,
        ];
    }
}
