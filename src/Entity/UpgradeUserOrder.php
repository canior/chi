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

    const JINQIU = 'JINQIU';
    const BIANXIAN = 'BIANXIAN';

    public static $typeText = [
        self::JINQIU => '金秋',
        self::BIANXIAN => '变现'
    ];


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
     * @var string
     * @ORM\Column(type="string")
     */
    private $type;

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
     * @var GroupUserOrder|null
     * @ORM\OneToOne(targetEntity="App\Entity\GroupUserOrder", mappedBy="upgradeUserOrder", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $groupUserOrder;


    /**
     * @var UpgradeOrderCoupon $upgradeOrderCoupon
     * @ORM\ManyToOne(targetEntity="App\Entity\UpgradeOrderCoupon", cascade={"persist"})
     */
    private $upgradeOrderCoupon;


    public function __construct()
    {
        $this->setTotal(0);
        $this->setCreatedAt();
        $this->setUpdatedAt();
        $this->upgradeUserOrderPayments = new ArrayCollection();
        $this->userAccountOrders = new ArrayCollection();
        $this->setType(self::JINQIU);
    }

    /**
     * UpgradeUserOrder constructor.
     * @parem string $type
     * @param $type
     * @param User $user
     * @param $newUserLevel
     * @param GroupUserOrder|null $groupUserOrder
     * @return UpgradeUserOrder
     */
    public static function factory($type, User $user, $newUserLevel, ?GroupUserOrder $groupUserOrder) {
        $upgradeUserOrder = new UpgradeUserOrder();
        $upgradeUserOrder->setType($type);
        $upgradeUserOrder->setUser($user);
        $upgradeUserOrder->setGroupUserOrder($groupUserOrder);
        if ($type == UpgradeUserOrder::JINQIU)
            $upgradeUserOrder->setOldUserLevel($user->getUserLevel());
        if ($type == UpgradeUserOrder::BIANXIAN)
            $upgradeUserOrder->setOldUserLevel($user->getBianxianUserLevel());
        $upgradeUserOrder->setUserLevel($newUserLevel);
        $upgradeUserOrder->setStatus(self::CREATED);
        if ($groupUserOrder) {
            $upgradeUserOrder->setTotal($groupUserOrder->getTotal());
        }
        return $upgradeUserOrder;
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
    }

    public function setPending() {
        $this->status = self::PENDING;
        $this->setUpdatedAt();
    }

    /**
     * 会员升级订单完成，分钱逻辑
     * 1. 升级会员
     * 2. 分钱给推荐人
     * 3. 分钱给直接讲师
     * 4. 分钱给间接讲师
     * @param bool $isPopulateUserAccount 是否触发分钱
     * @param UpgradeOrderCoupon|null $upgradeOrderCoupon 是否使用coupon
     */
    public function setApproved($isPopulateUserAccount = true, ?UpgradeOrderCoupon $upgradeOrderCoupon= null) {
        if ($this->isApproved())
            return;

        $this->status = self::APPROVED;
        $this->setUpdatedAt();

        $userLevel = $this->getUserLevel();
        $user = $this->getUser();

        /* 升级会员 */
        if ($this->isJinqiu()) {
            $user->upgradeUserLevel($userLevel);
            if ($isPopulateUserAccount)
                $this->populateUserAccountOrders($upgradeOrderCoupon);
        }

        if ($this->isBianxian()) {
            $user->upgradeBianxianUserLevel($userLevel);
            if ($isPopulateUserAccount)
                $this->populateBianxianUserAccountOrders();
        }
    }

    protected function populateBianxianUserAccountOrders() {
        if (!$this->isBianxian()) {
            return;
        }

        if (!$this->getGroupUserOrder()) {
            return;
        }

        if (!$this->getGroupUserOrder()->getProduct()->isCourseProduct()) {
            return;
        }

        if ($this->getGroupUserOrder()->getProduct()->getCourse()->getSubject() != Subject::TRADING) {
            return;
        }

        /* 分钱给合伙人推荐人 */
        $partnerUser = $this->getUser()->getParentUser();
        if ($partnerUser) {
            $rewards = Subject::$subjectRewards[$this->getGroupUserOrder()->getProduct()->getCourse()->getSubject()][$partnerUser->getBianxianUserLevel()];
            if ($this->isApproved()) {
                $memo = '推荐' . $this->getUser()->getNickname() . '系统课成功';
                $this->setRecommanderUser($partnerUser);
                $this->setPartnerUser($partnerUser);
                $partnerUser->createUserAccountOrder(UserAccountOrder::RECOMMAND_REWARDS, $rewards, $this, null, $memo);
            }
        }

        /* 分钱给最后一个思维课讲师 */
        $teacher = $this->getUser()->getTeacherRecommanderUser();
        if ($teacher) {
            $rewards = Subject::$subjectRewards[$this->getGroupUserOrder()->getProduct()->getCourse()->getSubject()]['THINKING_TEACHER'];
            if ($this->isApproved()) {
                $memo = '成交' . $this->getUser()->getNickname() . '系统课成功';
                $this->setPartnerTeacherUser($teacher);
                $teacher->createUserAccountOrder(UserAccountOrder::RECOMMAND_REWARDS, $rewards, $this, null, $memo);
            }
        }
    }

    /**
     * @param UpgradeOrderCoupon|null $upgradeOrderCoupon
     */
    protected function populateUserAccountOrders(?UpgradeOrderCoupon $upgradeOrderCoupon= null) {
        if (!$this->isJinqiu()) {
            return;
        }

        $user = $this->getUser();
        $userLevel = $this->getUserLevel();

        /* 分钱给供货商 */
        if ($this->getGroupUserOrder() != null) {
            $product = $this->getGroupUserOrder()->getProduct();
            if (!$product->isCourseProduct()) {
                $supplierUser = $product->getSupplierUser();
                $memo = '货款 ' . $product->getTitle();
                if ($product->getSupplierUser()) {
                    if ($this->isApproved()) {
                        $supplierUser->createUserAccountOrder(UserAccountOrder::SUPPLIER_REWARDS, $product->getSupplierPrice(), $this, null, $memo);
                    }
                }
            }
        }

        /* 分钱给推荐人 */
        $recommander = $user->getParentUser();
        if ($recommander) {
            $recommanderRewards = UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::ADVANCED];
            if ($this->isApproved()) {
                $this->setRecommanderUser($recommander);
                $memo = '推荐' . $user->getNickname() . '成为' . UserLevel::$userLevelTextArray[$userLevel];
                $recommander->createUserAccountOrder(UserAccountOrder::RECOMMAND_REWARDS, $recommanderRewards, $this, null, $memo);
            }
        }

        /* 分钱给合伙人 */
        $partner = $user->getTopParentPartnerUser();
        if ($partner) {
            $partnerRewards = UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER];
            if ($this->isApproved()) {
                $this->setPartnerUser($partner);
                $memo = $user->getNickname() . '成为' . UserLevel::$userLevelTextArray[$userLevel] ;
                $partner->createUserAccountOrder(UserAccountOrder::PARTNER_REWARDS, $partnerRewards, $this, null, $memo);
                //推荐名额减1
                //$partner->createUserRecommandStockOrder(-1, $this, $memo);
            }

            /* 分钱给合伙人的直接讲师 */
            $partnerTeacher = $partner->getPartnerTeacherRecommanderUser();
            if ($partnerTeacher) {
                $partnerTeacherRewards = UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER];
                if ($this->isApproved()) {
                    $this->setPartnerTeacherUser($partnerTeacher);
                    $memo = $user->getNickname() . '成为' . UserLevel::$userLevelTextArray[$userLevel];
                    $partnerTeacher->createUserAccountOrder(UserAccountOrder::PARTNER_TEACHER_REWARDS, $partnerTeacherRewards, $this, null, $memo);
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
    public function getType(): string
    {
        return $this->type;
    }/**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isJinqiu() {
        return self::JINQIU == $this->getType();
    }

    /**
     * @return bool
     */
    public function isBianxian() {
        return self::BIANXIAN == $this->getType();
    }

    /**
     * @return UpgradeOrderCoupon
     */
    public function getUpgradeOrderCoupon(): UpgradeOrderCoupon
    {
        return $this->upgradeOrderCoupon;
    }

    /**
     * @param UpgradeOrderCoupon $upgradeOrderCoupon
     */
    public function setUpgradeOrderCoupon(UpgradeOrderCoupon $upgradeOrderCoupon): void
    {
        $this->upgradeOrderCoupon = $upgradeOrderCoupon;
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
