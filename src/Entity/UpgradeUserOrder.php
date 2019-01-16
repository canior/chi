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

    const UNPAID = 'unpaid';
    const PAID = 'paid';

    const REFUNDING = 'refunding';
    const REFUNDED = 'refunded';

    public static $paymentStatusTexts = [
        self::PAID => '已支付',
        self::UNPAID => '未支付',
        self::REFUNDING => '退款中',
        self::REFUNDED => '已退款'
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
     * @var string
     * @ORM\Column(type="string")
     */
    private $paymentStatus;

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
     * @var string|null
     * @ORM\Column(type="string")
     */
    private $recommanderName;


    public function __construct()
    {
        $this->setTotal(0);
        $this->setCreatedAt();
        $this->setUpdatedAt();
        $this->upgradeUserOrderPayments = new ArrayCollection();
        $this->userAccountOrders = new ArrayCollection();
    }

    /**
     * UpgradeUserOrder constructor.
     * @param User $user
     * @param $userLevel
     * @param $total
     * @param $recommanderName
     * @return UpgradeUserOrder
     */
    public static function factory(User $user, $userLevel, $total, $recommanderName) {
        $upgradeUserOrder = new UpgradeUserOrder();
        $upgradeUserOrder->setUser($user);
        $upgradeUserOrder->setOldUserLevel($user->getUserLevel());
        $upgradeUserOrder->setUserLevel($userLevel);
        $upgradeUserOrder->setTotal($total);
        $upgradeUserOrder->setStatus(self::CREATED);
        $upgradeUserOrder->setPaymentStatus(self::UNPAID);
        $upgradeUserOrder->setUserLevel($userLevel);
        $upgradeUserOrder->setRecommanderName($recommanderName);
        return $upgradeUserOrder;
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
     * @return UpgradeUserOrderPayment[]
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
     * @return string
     */
    public function getPaymentStatusText() {
        return self::$paymentStatusTexts[$this->getPaymentStatus()];
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
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
     * @param float $amount
     * @param string $memo
     */
    public function addPayment($amount, $memo = null) {
        $payments = new UpgradeUserOrderPayment($this, $amount, $memo);
        $this->setPaid();
        $this->upgradeUserOrderPayments->add($payments);
    }

    public function setPaid() {
        $this->paymentStatus = self::PAID;
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
     */
    public function setApproved() {
        if ($this->status == self::APPROVED)
            return;

        $this->status = self::APPROVED;
        $userLevel = $this->getUserLevel();
        $user = $this->getUser();

        /* 升级会员 */
        $user->upgradeUserLevel($userLevel);

        /* 分钱给推荐人 */
        $recommander = $user->getParentUser();
        if ($recommander != null and $recommander->getRecommandStock() > 0) {
            $recommanderRewards = UserLevel::$userLevelRecommanderRewardsArray[$userLevel];
            $recommander->createUserAccountOrder(UserAccountOrder::RECOMMAND_REWARDS, $recommanderRewards, $this);
            //推荐名额减1
            $recommander->increaseRecommandStock(-1);
        }

        /* 分钱给直接讲师 */
        $latestCourse = $user->getLatestCourse();
        $currentSubject = $latestCourse->getSubject();
        if ($latestCourse != null) {
            $teacherRewards = Subject::$teacherRewards[$currentSubject][$this->getUserLevel()];
            $teacherUser = $latestCourse->getTeacher()->getUser();
            if ($teacherUser != null) {
                $teacherUser->createUserAccountOrder(UserAccountOrder::TEACHER_REWARDS, $teacherRewards, $this);
            }
        }

        /* 分钱给间接讲师 */
        if (array_key_exists($currentSubject, Subject::$oldTeacherRewards)) {
            foreach (Subject::$oldTeacherRewards[$currentSubject] as $oldSubjects) {
                $oldCourse = $user->getLatestCourse($oldSubjects);
                if ($oldCourse != null) {
                    if (array_key_exists($this->getUserLevel(), $oldSubjects)){
                        $oldTeacherRewards = $oldSubjects[$this->getUserLevel()];
                        $oldTeacherUser = $oldCourse->getTeacher()->getUser();
                        if ($oldTeacherUser != null) {
                            $oldTeacherUser->createUserAccountOrder(UserAccountOrder::OLD_TEACHER_REWARDS, $oldTeacherRewards, $this);
                        }
                    }
                }
            }
        }

        $this->setUpdatedAt();
    }

    public function setRejected() {
        $this->status = self::REJECTED;
        $this->setUpdatedAt();
    }

    /**
     * @return UserAccountOrder[]
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

    /**
     * @return string|null
     */
    public function getRecommanderName(): ?string
    {
        return $this->recommanderName;
    }

    /**
     * @param string|null $recommanderName
     */
    public function setRecommanderName($recommanderName): void
    {
        $this->recommanderName = $recommanderName;
    }

    public function getArray() {

        $upgradeUserOrderPaymentArray = [];
        foreach ($this->getUpgradeUserOrderPayments() as $payment) {
            $upgradeUserOrderPaymentArray[] = $payment->getArray();
        }
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->getArray(),
            'oldUserLevel' => $this->getOldUserLevel(),
            'oldUserLevelText' => $this->getOldUserLevelText(),
            'userLevel' => $this->getUserLevel(),
            'userLevelText' => $this->getUserLevelText(),
            'total' => $this->getTotal(),
            'status' => $this->getStatus(),
            'statusText' => $this->getStatusText(),
            'paymentStatus' => $this->getPaymentStatus(),
            'paymentStatusText' => $this->getPaymentStatusText(),
            'upgradeUserOrderPayments' => $upgradeUserOrderPaymentArray,
            'createdAt' => $this->getCreatedAt(true),
            'updatedAt' => $this->getUpdatedAt(true),
            'recommanderName' => $this->getRecommanderName(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '订单号: ' . $this->getId()
            . ' , 金额: ￥' . $this->getTotal()
            . ' , 状态:'. $this->getStatusText()
            . ' , 支付状态:'. $this->getPaymentStatusText();
    }
}