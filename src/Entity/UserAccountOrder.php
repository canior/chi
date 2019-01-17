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
     * @ORM\ManyToOne(targetEntity="App\Entity\UpgradeUserOrder", cascade={"persist"})
     */
    private $upgradeUserOrder;


    /**
     * @var string
     *
     * @ORM\Column(name="payment_status", type="string", length=50, nullable=false)
     */
    private $paymentStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="memo", type="string")
     */
    private $memo;

    /**
     * @param User $user
     * @param $userAccountOrderType
     * @param $amount
     * @param UpgradeUserOrder|null $upgradeUserOrder
     * @return UserAccountOrder
     */
    public static function factory(User $user, $userAccountOrderType, $amount, UpgradeUserOrder $upgradeUserOrder = null) {
        $userAccountOrder = new UserAccountOrder();
        $userAccountOrder->setUser($user);
        $userAccountOrder->setAmount($amount);
        $userAccountOrder->setUserAccountOrderType($userAccountOrderType);
        $userAccountOrder->setUpgradeUserOrder($upgradeUserOrder);
        if ($userAccountOrderType == UserAccountOrder::WITHDRAW)
            $user->decreaseUserAccountTotal($amount);
        else
            $user->increaseUserAccountTotal($amount);
        return $userAccountOrder;
    }

    /**
     * UserAccountOrder constructor.
     */
    public function __construct()
    {
        $this->setPaymentStatus(self::UNPAID);
        $this->setCreatedAt();
        $this->setUpdatedAt();
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
     * @return User|null
     */
    public function getUser(): ?User
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
     * @return float|null
     */
    public function getAmount(): ?float
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
    public function getUserAccountOrderTypeText() {
        return self::$userAccountOrderTypes[$this->getUserAccountOrderType()];
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    /**
     * @return string
     */
    public function getPaymentStatusText() {
        return self::$paymentStatuses[$this->getPaymentStatus()];
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus(string $paymentStatus): void
    {
        $this->paymentStatus = $paymentStatus;
        $this->setUpdatedAt();
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
        $this->setUpdatedAt();
    }

    /**
     * @return null|string
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @param null|string $memo
     */
    public function setMemo(?string $memo): void
    {
        $this->memo = $memo;
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
