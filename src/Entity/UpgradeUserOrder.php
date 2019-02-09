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
     * @param $total
     * @return UpgradeUserOrder
     */
    public static function factory(User $user, $userLevel, $total) {
        $upgradeUserOrder = new UpgradeUserOrder();
        $upgradeUserOrder->setUser($user);
        $upgradeUserOrder->setOldUserLevel($user->getUserLevel());
        $upgradeUserOrder->setUserLevel($userLevel);
        $upgradeUserOrder->setTotal($total);
        $upgradeUserOrder->setStatus(self::CREATED);
        $upgradeUserOrder->setUserLevel($userLevel);
        return $upgradeUserOrder;
    }

    /**
     * @return ArrayCollection|null
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
        $this->setPending();
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

        /* 分钱给推荐人 */
        $recommander = $user->getParentUser();
        if ($recommander != null and $recommander->getRecommandStock() > 0) {
            $recommanderRewards = UserLevel::$userLevelRecommanderRewardsArray[$userLevel];
            $memo = '推荐' . $user->getName() . '成为' . UserLevel::$userLevelTextArray[$userLevel] . $this->getUser()->getName();
            if ($this->isApproved()) {
                $recommander->createUserAccountOrder(UserAccountOrder::RECOMMAND_REWARDS, $recommanderRewards, $this, null, $memo);
                //推荐名额减1
                $recommander->createUserRecommandStockOrder(-1, $this, $memo);
            } else {
                $userAccountOrder = UserAccountOrder::factory($recommander, UserAccountOrder::RECOMMAND_REWARDS, $recommanderRewards, $this, null, $memo);
                //这里是虚拟账单，要把余额还回来
                $recommander->decreaseUserAccountTotal($recommanderRewards);
                $this->getPotentialUserAccountOrders()->add($userAccountOrder);
            }
        }

        /* 分钱给直接讲师 */
        $latestCourse = $user->getLatestCourse();
        if (!$latestCourse)
            return;

        $currentSubject = $latestCourse->getSubject();
        if ($latestCourse != null) {
            $teacherRewards = Subject::$teacherRewards[$currentSubject][$this->getUserLevel()];
            $teacherUser = $latestCourse->getTeacher()->getUser();
            if ($teacherUser != null) {
                $memo = '直接促成' . UserLevel::$userLevelTextArray[$userLevel] . $this->getUser()->getName();
                if ($this->isApproved()){
                    $teacherUser->createUserAccountOrder(UserAccountOrder::TEACHER_REWARDS, $teacherRewards, $this, $latestCourse, $memo);
                } else {
                    $userAccountOrder = UserAccountOrder::factory($teacherUser, UserAccountOrder::TEACHER_REWARDS, $teacherRewards, $this, $latestCourse, $memo);
                    //这里是虚拟账单，要把余额还回来
                    $teacherUser->decreaseUserAccountTotal($teacherRewards);
                    $this->getPotentialUserAccountOrders()->add($userAccountOrder);
                }
            }
        }

        /* 分钱给间接讲师 */
        if (array_key_exists($currentSubject, Subject::$oldTeacherRewards)) {
            foreach (Subject::$oldTeacherRewards[$currentSubject] as $oldSubject => $oldSubjectConfigs) {
                $oldCourse = $user->getLatestCourse($oldSubject);
                if ($oldCourse != null) {
                    $oldTeacherUser = $oldCourse->getTeacher()->getUser();
                    if ($oldTeacherUser != null) {
                        $memo = '间接促成' . UserLevel::$userLevelTextArray[$userLevel] . $this->getUser()->getName();
                        if ($this->isApproved()) {
                            $oldTeacherUser->createUserAccountOrder(UserAccountOrder::OLD_TEACHER_REWARDS, $oldSubjectConfigs[$this->getUserLevel()], $this, $oldCourse, $memo);
                        } else {
                            $userAccountOrder = UserAccountOrder::factory($oldTeacherUser, UserAccountOrder::OLD_TEACHER_REWARDS, $oldSubjectConfigs[$this->getUserLevel()], $this, $oldCourse, $memo);
                            //这里是虚拟账单，要把余额还回来
                            $oldTeacherUser->decreaseUserAccountTotal($oldSubjectConfigs[$this->getUserLevel()]);
                            $this->getPotentialUserAccountOrders()->add($userAccountOrder);
                        }
                    }
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
            . ' , 姓名: ' . $this->getUser()->getName()
            . ' , 升级: ' . $this->getUserLevelText()
            . ' , 金额: ￥' . $this->getTotal()
            . ' , 状态:'. $this->getStatusText();
    }
}
