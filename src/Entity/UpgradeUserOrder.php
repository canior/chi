<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 7:59 PM
 */

namespace App\Entity;


use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\UpgradeUserOrderPayment;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Monolog\Logger;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UpgradeUserOrderRepository")
 */
class UpgradeUserOrder implements Dao
{
    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    const CREATED = 'created';
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';


    public static $statusTexts = [
        self::CREATED => '已创建',
        self::PENDING => '处理中',
        self::APPROVED => '已通过',
        self::REJECTED => '已拒绝',
    ];

    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="upgradeUserOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var User|null $recommanderUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $recommanderUser;

    /**
     * @var User|null $partnerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $partnerUser;

    /**
     * @var User|null $partnerTeacherUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $partnerTeacherUser;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $userLevel;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @var UpgradeUserOrderPayment[]
     * @ORM\OneToMany(targetEntity="App\Entity\UpgradeUserOrderPayment", mappedBy="upgradeUserOrder", cascade={"persist"}, orphanRemoval=true, fetch="LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $upgradeUserOrderPayments;


    /**
     * @var UserAccountOrder[]
     * @ORM\OneToMany(targetEntity="App\Entity\UserAccountOrder", mappedBy="upgradeUserOrder", cascade={"persist"}, orphanRemoval=true, fetch="LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userAccountOrders;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $oldUserLevel;

    /**
     * @var ArrayCollection|null
     */
    private $potentialUserAccountOrders;

    /**
     * @var GroupUserOrder|null
     * @ORM\OneToOne(targetEntity="App\Entity\GroupUserOrder", mappedBy="upgradeUserOrder", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $groupUserOrder;


    public function __construct()
    {
        $this->setTotal(0);
        $this->setCreatedAt();
        $this->setUpdatedAt();
        $this->upgradeUserOrderPayments = new ArrayCollection();
        $this->userAccountOrders = new ArrayCollection();
        $this->potentialUserAccountOrders = new ArrayCollection();
    }

    /**
     * UpgradeUserOrder constructor.
     * @param User $user
     * @param $userLevel
     * @param GroupUserOrder|null $groupUserOrder
     * @return UpgradeUserOrder
     */
    public static function factory(User $user, $userLevel, GroupUserOrder $groupUserOrder) {
        $upgradeUserOrder = new UpgradeUserOrder();
        $upgradeUserOrder->setUser($user);
        $upgradeUserOrder->setGroupUserOrder($groupUserOrder);
        $upgradeUserOrder->setOldUserLevel($user->getUserLevel());
        $upgradeUserOrder->setUserLevel($userLevel);
        $upgradeUserOrder->setTotal($groupUserOrder->getTotal());
        $upgradeUserOrder->setStatus(self::CREATED);
        $upgradeUserOrder->setUserLevel($userLevel);

        $groupUserOrder->setUpgradeUserOrder($upgradeUserOrder);

        return $upgradeUserOrder;
    }

    /**
     * @return ArrayCollection|UserAccountOrder[]|null
     */
    public function getPotentialUserAccountOrders(): ?ArrayCollection
    {
        if ($this->potentialUserAccountOrders == null) {
            $this->potentialUserAccountOrders = new ArrayCollection();
            $this->populateUserAccountOrders();
        }
        return $this->potentialUserAccountOrders;
    }

    /**
     * @param ArrayCollection|null $potentialUserAccountOrders
     */
    public function setPotentialUserAccountOrders(?ArrayCollection $potentialUserAccountOrders): void
    {
        $this->potentialUserAccountOrders = $potentialUserAccountOrders;
    }

    /**
     * @return bool
     */
    public function isCreated() {
        return $this->getStatus() == self::CREATED;
    }

    /**
     * @return bool
     */
    public function isApproved() {
        return $this->getStatus() == self::APPROVED;
    }


    /**
     * @return bool
     */
    public function isPending() {
        return $this->getStatus() == self::PENDING;
    }

    /**
     * @return bool
     */
    public function isRejected() {
        return $this->getStatus() == self::REJECTED;
    }

    /**
     * @return string
     */
    public function getOldUserLevel(): string
    {
        return $this->oldUserLevel;
    }

    /**
     * @param string $oldUserLevel
     */
    public function setOldUserLevel(string $oldUserLevel): void
    {
        $this->oldUserLevel = $oldUserLevel;
    }

    /**
     * @return string
     */
    public function getOldUserLevelText() {
        return UserLevel::$userLevelTextArray[$this->getOldUserLevel()];
    }

    /**
     * @return UpgradeUserOrderPayment[]|ArrayCollection
     */
    public function getUpgradeUserOrderPayments()
    {
        return $this->upgradeUserOrderPayments;
    }

    /**
     * @param UpgradeUserOrderPayment[] $upgradeUserOrderPayments
     */
    public function setUpgradeUserOrderPayments($upgradeUserOrderPayments): void
    {
        $this->upgradeUserOrderPayments = $upgradeUserOrderPayments;
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
    public function getUserLevel(): string
    {
        return $this->userLevel;
    }

    /**
     * @return string
     */
    public function getUserLevelText() {
        return UserLevel::$userLevelTextArray[$this->getUserLevel()];
    }

    /**
     * @param string $userLevel
     */
    public function setUserLevel(string $userLevel): void
    {
        $this->userLevel = $userLevel;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusText() {
        return self::$statusTexts[$this->getStatus()];
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        switch ($status) {
            case self::CREATED: $this->setCreated(); return;
            case self::PENDING: $this->setPending(); return;
            case self::APPROVED: $this->setApproved(); return;
            case self::REJECTED: $this->setRejected(); return;
        }

    }

    /**
     * @param float $amount
     * @param string $memo
     */
    public function addPayment($amount, $memo = null) {
        $payments = UpgradeUserOrderPayment::factory($this, $amount, $memo);
        $this->upgradeUserOrderPayments->add($payments);
    }

    public function setCreated() {
        $this->status = self::CREATED;
        $this->setUpdatedAt();
        $this->populateUserAccountOrders();
    }

    public function setPending() {
        $this->status = self::PENDING;
        $this->setUpdatedAt();
        $this->populateUserAccountOrders();
    }

    /**
     * 会员升级订单完成，分钱逻辑
     * 1. 升级会员
     * 2. 分钱给推荐人
     * 3. 分钱给直接讲师
     * 4. 分钱给间接讲师
     */
    public function setApproved() {
        if ($this->isApproved())
            return;

        $this->status = self::APPROVED;
        $this->setUpdatedAt();

        $userLevel = $this->getUserLevel();
        $user = $this->getUser();

        /* 升级会员 */
        $user->upgradeUserLevel($userLevel);
        /* 更新名额 */
        $increasedStock = UserLevel::$userLevelRecommanderStockArray[$userLevel];

        if ($increasedStock > 0) {
            $memo = '升级至' . UserLevel::$userLevelTextArray[$userLevel];
            $user->createUserRecommandStockOrder($increasedStock, $this, $memo);
        }

        $this->populateUserAccountOrders();
    }

    protected function populateUserAccountOrders() {

        $user = $this->getUser();
        $userLevel = $this->getUserLevel();

        /* 分钱给供货商 */
        $product = $this->getGroupUserOrder()->getProduct();
        $supplierUser = $product->getSupplierUser();
        $memo = '货款 ' . $product->getTitle();
        if ($product->getSupplierUser()) {
            if ($this->isApproved()) {
                $supplierUser->createUserAccountOrder(UserAccountOrder::SUPPLIER_REWARDS, $product->getSupplierPrice(), $this, null, $memo);
            } else {
                $userAccountOrder = UserAccountOrder::factory($supplierUser, UserAccountOrder::SUPPLIER_REWARDS, $product->getSupplierPrice(), $this, null, $memo);
                //这里是虚拟账单，要把余额还回来
                $supplierUser->decreaseUserAccountTotal($product->getSupplierPrice());
                $this->getPotentialUserAccountOrders()->add($userAccountOrder);
            }
        }

        /* 分钱给推荐人 */
        $recommander = $user->getParentUser();
        if ($recommander) {
            $recommanderRewards = UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::ADVANCED];
            if ($this->isApproved()) {
                $this->setRecommanderUser($recommander);
                $memo = '推荐' . $user->getName() . '成为' . UserLevel::$userLevelTextArray[$userLevel] . $this->getUser()->getName();
                $recommander->createUserAccountOrder(UserAccountOrder::RECOMMAND_REWARDS, $recommanderRewards, $this, null, $memo);
            } else {
                $userAccountOrder = UserAccountOrder::factory($recommander, UserAccountOrder::RECOMMAND_REWARDS, $recommanderRewards, $this, null, $memo);
                //这里是虚拟账单，要把余额还回来
                $recommander->decreaseUserAccountTotal($recommanderRewards);
                $this->getPotentialUserAccountOrders()->add($userAccountOrder);
            }
        }

        /* 分钱给合伙人 */
        $partner = $user->getTopParentPartnerUser();
        if ($partner) {
            $partnerRewards = UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER];
            if ($this->isApproved()) {
                $this->setPartnerUser($partner);
                $memo = $user->getName() . '成为' . UserLevel::$userLevelTextArray[$userLevel] . $this->getUser()->getName();
                $partner->createUserAccountOrder(UserAccountOrder::PARTNER_REWARDS, $partnerRewards, $this, null, $memo);
                //推荐名额减1
                $partner->createUserRecommandStockOrder(-1, $this, $memo);
            } else {
                $userAccountOrder = UserAccountOrder::factory($partner, UserAccountOrder::PARTNER_REWARDS, $partnerRewards, $this, null, $memo);
                //这里是虚拟账单，要把余额还回来
                $partner->decreaseUserAccountTotal($partnerRewards);
                $this->getPotentialUserAccountOrders()->add($userAccountOrder);
            }

            /* 分钱给合伙人的直接讲师 */
            $partnerTeacher = $partner->getTeacherRecommanderUser();
            if ($partnerTeacher) {
                $partnerTeacherRewards = UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER];
                if ($this->isApproved()) {
                    $this->setPartnerTeacherUser($partnerTeacher);
                    $memo = $user->getName() . '成为' . UserLevel::$userLevelTextArray[$userLevel] . $this->getUser()->getName();
                    $partnerTeacher->createUserAccountOrder(UserAccountOrder::PARTNER_TEACHER_REWARDS, $partnerTeacherRewards, $this, null, $memo);
                } else {
                    $userAccountOrder = UserAccountOrder::factory($partnerTeacher, UserAccountOrder::PARTNER_TEACHER_REWARDS, $partnerTeacherRewards, $this, null, $memo);
                    //这里是虚拟账单，要把余额还回来
                    $partnerTeacher->decreaseUserAccountTotal($partnerTeacherRewards);
                    $this->getPotentialUserAccountOrders()->add($userAccountOrder);
                }
            }
        }

    }


    public function setRejected() {
        $this->status = self::REJECTED;
        $this->setUpdatedAt();
    }

    /**
     * @return UserAccountOrder[]|ArrayCollection
     */
    public function getUserAccountOrders()
    {
        return $this->userAccountOrders;
    }

    /**
     * @param UserAccountOrder[] $userAccountOrders
     */
    public function setUserAccountOrders($userAccountOrders): void
    {
        $this->userAccountOrders = $userAccountOrders;
    }

    public function getTotalPaymentAmount() {
        $totalAmount = 0;
        foreach ($this->upgradeUserOrderPayments as $upgradeUserOrderPayment) {
            $totalAmount += $upgradeUserOrderPayment->getAmount();
        }
        return $totalAmount;
    }

    /**
     * @return GroupUserOrder|null
     */
    public function getGroupUserOrder(): ?GroupUserOrder
    {
        return $this->groupUserOrder;
    }

    /**
     * @param GroupUserOrder|null $groupUserOrder
     */
    public function setGroupUserOrder(?GroupUserOrder $groupUserOrder): void
    {
        $this->groupUserOrder = $groupUserOrder;
    }

    /**
     * @return User|null
     */
    public function getRecommanderUser(): ?User
    {
        return $this->recommanderUser;
    }

    /**
     * @param User|null $recommanderUser
     */
    public function setRecommanderUser(?User $recommanderUser): void
    {
        $this->recommanderUser = $recommanderUser;
    }

    /**
     * @return User|null
     */
    public function getPartnerUser(): ?User
    {
        return $this->partnerUser;
    }

    /**
     * @param User|null $partnerUser
     */
    public function setPartnerUser(?User $partnerUser): void
    {
        $this->partnerUser = $partnerUser;
    }

    /**
     * @return User|null
     */
    public function getPartnerTeacherUser(): ?User
    {
        return $this->partnerTeacherUser;
    }

    /**
     * @param User|null $partnerTeacherUser
     */
    public function setPartnerTeacherUser(?User $partnerTeacherUser): void
    {
        $this->partnerTeacherUser = $partnerTeacherUser;
    }

    public function getArray() {

        $upgradeUserOrderPaymentArray = [];
        foreach ($this->getUpgradeUserOrderPayments() as $payment) {
            $upgradeUserOrderPaymentArray[] = $payment->getArray();
        }
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->getArray(),
            'recommanderUser' => $this->getRecommanderUser() ? $this->getRecommanderUser()->getArray() : null,
            'partnerUser' => $this->getPartnerUser() ? $this->getPartnerUser()->getArray() : null,
            'partnerTeacherUser' => $this->getPartnerTeacherUser() ? $this->getPartnerTeacherUser()->getArray() : null,
            'oldUserLevel' => $this->getOldUserLevel(),
            'oldUserLevelText' => $this->getOldUserLevelText(),
            'userLevel' => $this->getUserLevel(),
            'userLevelText' => $this->getUserLevelText(),
            'total' => $this->getTotal(),
            'status' => $this->getStatus(),
            'statusText' => $this->getStatusText(),
            'upgradeUserOrderPayments' => $upgradeUserOrderPaymentArray,
            'createdAt' => $this->getCreatedAt(true),
            'updatedAt' => $this->getUpdatedAt(true),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ' 订单号: ' . $this->getId()
            . ' , 用户: ' . $this->getUser()
            . ' , 升级: ' . $this->getOldUserLevelText() . '->' . $this->getUserLevelText()
            . ' , 金额: ￥' . $this->getTotal()
            . ' , 状态:'. $this->getStatusText();
    }
}
