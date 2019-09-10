<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Service\Util\CommonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use App\Service\Util\FactoryUtil;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser implements Dao
{
    const PARENT_45_DAYS_EXPIRES_SECONDS = 3888000; //锁定45天
    const PARENT_365_DAYS_EXPIRES_SECONDS = 31536000; //锁定365天

    public static $lockTimeTextArray = [
        self::PARENT_45_DAYS_EXPIRES_SECONDS => '45天',
        self::PARENT_365_DAYS_EXPIRES_SECONDS => '365天',
    ];

    const ROLE_CUSTOMER = 'ROLE_CUSTOMER';
    const ROLE_SECURITY = 'ROLE_SECURITY';
    const ROLE_CUSTOMER_SERVICE = 'ROLE_CUSTOMER_SERVICE';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public static $roleTexts = [
        self::ROLE_CUSTOMER => '客户',
        self::ROLE_SECURITY => '课程检票',
        self::ROLE_CUSTOMER_SERVICE => '客服',
        self::ROLE_ADMIN => '管理员',
        self::ROLE_SUPER_ADMIN => '超级管理员',

    ];

    public static $adminRoleTexts = [
        self::ROLE_CUSTOMER => '客户',
        self::ROLE_SECURITY => '课程检票',
        self::ROLE_CUSTOMER_SERVICE => '客服',
        self::ROLE_ADMIN => '管理员',
        self::ROLE_SUPER_ADMIN => '超级管理员',
    ];

    use CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nickname;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $totalRewards;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $wxOpenId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $wxGzhOpenId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $wxUnionId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatarUrl;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Region", cascade={"persist", "remove"})
     */
    private $region;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subUsers", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_user_id", referencedColumnName="id")
     */
    private $parentUser;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="parentUser", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $subUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserActivity", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userActivities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAddress", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userAddresses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupOrder", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $groupOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrder", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $groupUserOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrderRewards", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $groupUserOrderRewards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSource", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $shareSources;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserStatistics", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userStatistics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSourceUser", mappedBy="user", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     * @var ArrayCollection|ShareSourceUser[] $shareSourceUsers
     */
    private $shareSourceUsers;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $pendingTotalRewards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CommandMessage", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userCommands;


    /**
     * @var Teacher
     * @ORM\OneToOne(targetEntity="App\Entity\Teacher", inversedBy="user", cascade={"persist"})
     */
    private $teacher;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $userLevel;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $bianxianUserLevel;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    private $userAccountTotal;

    /**
     * @var int
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    private $recommandStock;

    /**
     * @var UserAccountOrder[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\UserAccountOrder", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userAccountOrders;

    /**
     * @var UpgradeUserOrder[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\UpgradeUserOrder", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $upgradeUserOrders;

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     */
    private $parentUserExpiresAt;


    /**
     * @var CourseStudent[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\CourseStudent", mappedBy="studentUser", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $courseStudents;


    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idNum;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $wechat;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private $recommanderName;


    /**
     * @var UserRecommandStockOrder[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\UserRecommandStockOrder", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userRecommandStockOrders;

    /**
     * @var Product[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="supplierUser", cascade={"persist"}, orphanRemoval=false, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $supplierProducts;


    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="recommandStudentUsers", cascade={"persist"})
     * @ORM\JoinColumn(name="teacher_recommander_user_id", referencedColumnName="id")
     */
    private $teacherRecommanderUser;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="partner_teacher_recommander_user_id", referencedColumnName="id")
     */
    private $partnerTeacherRecommanderUser;


    /**
     * @var ArrayCollection|User[]
     */
    private $recommandStudentUsers;


    /**
     * @var ArrayCollection|UserParentLog[] $userParentLogs
     * @ORM\OneToMany(targetEntity="App\Entity\UserParentLog", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userParentLogs;

    /**
     * @var ArrayCollection|UserParentLog[] $userLogs
     * @ORM\OneToMany(targetEntity="App\Entity\UserLog", mappedBy="user", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $userLogs;

    /**
     * @var text
     * @ORM\Column(type="string")
     */
    private $bank;

    /**
     * @var text
     * @ORM\Column(type="string")
     */
    private $bankAccountNumber;

    /**
     * @var text
     * @ORM\Column(type="string")
     */
    private $bankAccountName;


    public function __construct()
    {
        parent::__construct();

        $this->setCreatedAt();
        $this->setEnabled(true);
        $this->setTotalRewards(0);
        $this->setPendingTotalRewards(0);
        $this->setRoles([self::ROLE_CUSTOMER]);
        $this->subUsers = new ArrayCollection();
        $this->userActivities = new ArrayCollection();
        $this->userAddresses = new ArrayCollection();
        $this->groupOrders = new ArrayCollection();
        $this->groupUserOrders = new ArrayCollection();
        $this->groupUserOrderRewards = new ArrayCollection();
        $this->shareSources = new ArrayCollection();
        $this->setUpdatedAt();
        $this->userStatistics = new ArrayCollection();
        $this->shareSourceUsers = new ArrayCollection();
        $this->userCommands = new ArrayCollection();
        $this->supplierProducts = new ArrayCollection();

        $this->setUserLevel(UserLevel::VISITOR);
        $this->setBianxianUserLevel(BianxianUserLevel::VISITOR);
        $this->setUserAccountTotal(0);
        $this->setRecommandStock(0);

        $this->upgradeUserOrders = new ArrayCollection();
        $this->userAccountOrders = new ArrayCollection();
        $this->courseStudents = new ArrayCollection();
        $this->userRecommandStockOrders = new ArrayCollection();
        $this->recommandStudentUsers = new ArrayCollection();
        $this->userParentLogs = new ArrayCollection();
        $this->userLogs = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CourseStudent[]|ArrayCollection|Collection
     */
    public function getCourseStudents()
    {
        return $this->courseStudents;
    }

    /**
     * @param CourseStudent $courseStudent
     */
    public function addCourseStudent(CourseStudent $courseStudent)
    {
        $this->courseStudents->add($courseStudent);
    }

    /**
     * @return bool
     */
    public function isTeacher()
    {
        return $this->getTeacher() != null;
    }

    /**
     * @return bool
     */
    public function isVisitorUser()
    {
        return $this->getUserLevel() == UserLevel::VISITOR and $this->getBianxianUserLevel() == UserLevel::VISITOR;
    }

    /**
     * @return bool
     */
    public function hasAdminRoles()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN)
            or $this->hasRole(static::ROLE_ADMIN)
            or $this->hasRole(static::ROLE_CUSTOMER_SERVICE)
            or $this->hasRole(static::ROLE_SECURITY);
    }

    /**
     * @return bool
     */
    public function isAdvancedUser()
    {
        return $this->getUserLevel() == UserLevel::ADVANCED or
            $this->getUserLevel() == UserLevel::ADVANCED2 or
            $this->getUserLevel() == UserLevel::ADVANCED3;
    }

    /**
     * @return bool
     */
    public function isBianxianAdvancedUser()
    {
        return $this->getBianxianUserLevel() == BianxianUserLevel::ADVANCED;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return !$this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isPartnerUser()
    {
        return $this->getUserLevel() == UserLevel::PARTNER;
    }

    /**
     * @return bool
     */
    public function isBianxianPartnerUser()
    {
        return $this->getBianxianUserLevel() == BianxianUserLevel::PARTNER;
    }

    /**
     * @return bool
     * @author zxqc2018
     */
    public function isBianxianPartnerUpUser()
    {
        return BianxianUserLevel::PARTNER == $this->getBianxianUserLevel() || BianxianUserLevel::DISTRIBUTOR == $this->getBianxianUserLevel();
    }

    /**
     * @return bool
     */
    public function isNewUser()
    {
        return BianxianUserLevel::VISITOR == $this->getBianxianUserLevel() || BianxianUserLevel::THINKING == $this->getBianxianUserLevel();
    }

    /**
     * 是否有推荐名额资格
     * @return bool
     * @author zxqc2018
     */
    public function isHasRecommendStockLevel()
    {
        return in_array($this->getBianxianUserLevel(), [BianxianUserLevel::PARTNER, BianxianUserLevel::DISTRIBUTOR]);
    }

    public function getRoleText()
    {
        return join(array_intersect_key(self::$roleTexts, array_flip($this->getRoles())), ', ');
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getTotalRewards(): ?float
    {
        return $this->totalRewards;
    }

    public function setTotalRewards(?float $totalRewards): self
    {
        $this->totalRewards = $totalRewards;

        return $this;
    }

    public function getWxOpenId(): ?string
    {
        return $this->wxOpenId;
    }

    public function setWxOpenId(?string $wxOpenId): self
    {
        $this->wxOpenId = $wxOpenId;

        return $this;
    }

    /**
     * @return string
     */
    public function getWxGzhOpenId(): ?string
    {
        return $this->wxGzhOpenId ?? '';
    }

    /**
     * @param string $wxGzhOpenId
     */
    public function setWxGzhOpenId(?string $wxGzhOpenId): void
    {
        $this->wxGzhOpenId = $wxGzhOpenId;
    }

    public function getWxUnionId(): ?string
    {
        return $this->wxUnionId;
    }

    public function setWxUnionId(?string $wxUnionId): self
    {
        $this->wxUnionId = $wxUnionId;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getParentUser(): ?self
    {
        return $this->parentUser;
    }

    /**
     * @param null|self $parentUser
     * @param ShareSource|null $shareSource
     * @param null|string $memo
     * @return User
     */
    public function setParentUser(?User $parentUser, ShareSource $shareSource = null,  $memo = null): self
    {
        if ($parentUser == $this) {
            $this->info('same parent, ignore');
            return $this;
        }

        $this->parentUser = $parentUser;

        if ($memo != null) {
            $this->info('found a reason to change parent user');
            $userParentLog = UserParentLog::factory($this, $parentUser, $shareSource, $memo);
            $this->addUserParentLog($userParentLog);
        }

        return $this;
    }

    /**
     * Get subUsers
     *
     * @return Collection|User[]
     */
    public function getSubUsers(): Collection
    {
        return $this->subUsers;
    }

    /**
     * 有效的下线用户
     *
     * @return int
     */
    public function getTotalChildren()
    {
        return $this->getSubUsers()->count();
    }

    /**
     * 曾经用掉的总名额 + 剩余名额 = 曾经拥有的总名额
     *
     * @return int
     */
    public function getTotalRecommandStock()
    {
        return $this->getRecommandStock() + $this->getUsedRecommandStockCount();
    }

    /**
     * @param User $subUser
     * @param int $parentExpiresAt
     * @return User
     */
    public function addSubUser(User $subUser, $parentExpiresAt): self
    {
        if (!$this->subUsers->contains($subUser)) {
            $this->subUsers[] = $subUser;
            $subUser->setParentUser($this);
            $subUser->setParentUserExpiresAt($parentExpiresAt);
        }

        return $this;
    }

    /**
     * @param User $subUser
     * @return User
     */
    public function removeSubUser(User $subUser): self
    {
        if ($this->subUsers->contains($subUser)) {
            $this->subUsers->removeElement($subUser);
            // set the owning side to null (unless already changed)
            if ($subUser->getParentUser() === $this) {
                $subUser->setParentUser(null);
                $subUser->setParentUserExpiresAt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserActivity[]
     */
    public function getUserActivities(): Collection
    {
        return $this->userActivities;
    }

    public function addUserActivity(UserActivity $userActivity): self
    {
        if (!$this->userActivities->contains($userActivity)) {
            $this->userActivities[] = $userActivity;
            $userActivity->setUser($this);
        }

        return $this;
    }

    public function removeUserActivity(UserActivity $userActivity): self
    {
        if ($this->userActivities->contains($userActivity)) {
            $this->userActivities->removeElement($userActivity);
            // set the owning side to null (unless already changed)
            if ($userActivity->getUser() === $this) {
                $userActivity->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserAddress[]
     */
    public function getUserAddresses(): Collection
    {
        return $this->userAddresses;
    }

    public function getActiveUserAddress(): Collection
    {
        $userAddresses = new ArrayCollection();
        foreach ($this->getUserAddresses() as $userAddress) {
            if (!$userAddress->getIsDeleted()) {
                $userAddresses[] = $userAddress;
            }
        }
        return $userAddresses;
    }

    public function addUserAddress(UserAddress $userAddress): self
    {
        if (!$this->userAddresses->contains($userAddress)) {
            $this->userAddresses[] = $userAddress;
            $userAddress->setUser($this);
        }

        return $this;
    }

    public function removeUserAddress(UserAddress $userAddress): self
    {
        if ($this->userAddresses->contains($userAddress)) {
            $this->userAddresses->removeElement($userAddress);
            // set the owning side to null (unless already changed)
            if ($userAddress->getUser() === $this) {
                $userAddress->setUser(null);
            }
        }

        return $this;
    }

    /**
     * 获取用户的默认地址
     * @return UserAddress
     */
    public function getDefaultUserAddress(): ?UserAddress
    {
        foreach ($this->getUserAddresses() as $ua) {
            if ($ua->getIsDefault()) return $ua;
        }
        return null;
    }

    /**
     * @return Collection|GroupOrder[]
     */
    public function getGroupOrders(): Collection
    {
        return $this->groupOrders;
    }

    public function addGroupOrder(GroupOrder $groupBuying): self
    {
        if (!$this->groupOrders->contains($groupBuying)) {
            $this->groupOrders[] = $groupBuying;
            $groupBuying->setUser($this);
        }

        return $this;
    }

    public function removeGroupOrder(GroupOrder $groupBuying): self
    {
        if ($this->groupOrders->contains($groupBuying)) {
            $this->groupOrders->removeElement($groupBuying);
            // set the owning side to null (unless already changed)
            if ($groupBuying->getUser() === $this) {
                $groupBuying->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GroupUserOrder[]
     */
    public function getGroupUserOrders(): Collection
    {
        return $this->groupUserOrders;
    }

    public function addGroupUserOrder(GroupUserOrder $groupUserOrder): self
    {
        if (!$this->groupUserOrders->contains($groupUserOrder)) {
            $this->groupUserOrders[] = $groupUserOrder;
            $groupUserOrder->setUser($this);
        }

        return $this;
    }

    public function removeGroupUserOrder(GroupUserOrder $groupUserOrder): self
    {
        if ($this->groupUserOrders->contains($groupUserOrder)) {
            $this->groupUserOrders->removeElement($groupUserOrder);
            // set the owning side to null (unless already changed)
            if ($groupUserOrder->getUser() === $this) {
                $groupUserOrder->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GroupUserOrderRewards[]
     */
    public function getGroupUserOrderRewards(): Collection
    {
        return $this->groupUserOrderRewards;
    }

    public function addGroupUserOrderReward(GroupUserOrderRewards $groupOrderReward): self
    {
        if (!$this->groupUserOrderRewards->contains($groupOrderReward)) {
            $this->groupUserOrderRewards[] = $groupOrderReward;
            $groupOrderReward->setUser($this);
        }

        return $this;
    }

    public function removeGroupUserOrderReward(GroupUserOrderRewards $groupOrderReward): self
    {
        if ($this->groupUserOrderRewards->contains($groupOrderReward)) {
            $this->groupUserOrderRewards->removeElement($groupOrderReward);
            // set the owning side to null (unless already changed)
            if ($groupOrderReward->getUser() === $this) {
                $groupOrderReward->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return null|GroupUserOrder
     */
    public function getLastRewardsGroupUserOrder()
    {
        /**
         * @var GroupUserOrder $lastGroupUserOrder
         */
        $lastGroupUserOrder = $this->groupUserOrders->isEmpty() ? null : $this->groupUserOrders->first();
        /**
         * @var GroupUserOrderRewards $lastGroupUserOrderReward
         */
        $lastGroupUserOrderReward = $this->groupUserOrderRewards->isEmpty() ? null : $this->groupUserOrderRewards->first();
        if ($lastGroupUserOrder && $lastGroupUserOrderReward) {
            return $lastGroupUserOrder->getCreatedAt(false) > $lastGroupUserOrderReward->getGroupUserOrder()->getCreatedAt(false)
                ? $lastGroupUserOrder
                : $lastGroupUserOrderReward->getGroupUserOrder();
        } elseif ($lastGroupUserOrder) {
            return $lastGroupUserOrder;
        } elseif ($lastGroupUserOrderReward) {
            return $lastGroupUserOrderReward->getGroupUserOrder();
        }
        return null;
    }

    /**
     * @return Collection|ShareSource[]
     */
    public function getShareSources(): Collection
    {
        return $this->shareSources;
    }

    /**
     * @return User[]|Collection
     */
    public function getSharedUsers() {
        $sharedUsers = new ArrayCollection();
        foreach($this->getShareSources() as $shareSource) {
            foreach($shareSource->getShareSourceUsers() as $shareSourceUser) {
                $user = $shareSourceUser->getUser();
                if (!$sharedUsers->contains($user)) {
                    $sharedUsers->add($user);
                }
            }
        }
        return $sharedUsers;
    }

    /**
     * @return int
     */
    public function getTotalSharedUsers() {
        return $this->getSharedUsers()->count();
    }


    public function addShareSource(ShareSource $shareSource): self
    {
        if (!$this->shareSources->contains($shareSource)) {
            $this->shareSources[] = $shareSource;
            $shareSource->setUser($this);
        }

        return $this;
    }

    public function removeShareSource(ShareSource $shareSource): self
    {
        if ($this->shareSources->contains($shareSource)) {
            $this->shareSources->removeElement($shareSource);
            // set the owning side to null (unless already changed)
            if ($shareSource->getUser() === $this) {
                $shareSource->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserStatistics[]
     */
    public function getUserStatistics(): Collection
    {
        return $this->userStatistics;
    }

    /**
     * @return UserStatistics
     */
    public function getOrCreateTodayUserStatistics()
    {
        foreach ($this->getUserStatistics() as $userStatistics) {
            if ($userStatistics->isToday()) {
                return $userStatistics;
            }
        }
        $userStatistics = new UserStatistics($this);
        $this->addUserStatistic($userStatistics);
        return $userStatistics;
    }

    public function addUserStatistic(UserStatistics $userStatistic): self
    {
        if (!$this->userStatistics->contains($userStatistic)) {
            $this->userStatistics[] = $userStatistic;
            $userStatistic->setUser($this);
        }

        return $this;
    }

    public function removeUserStatistic(UserStatistics $userStatistic): self
    {
        if ($this->userStatistics->contains($userStatistic)) {
            $this->userStatistics->removeElement($userStatistic);
            // set the owning side to null (unless already changed)
            if ($userStatistic->getUser() === $this) {
                $userStatistic->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @param UserAddress $userAddress
     */
    public function setDefaultAddress(UserAddress $userAddress)
    {
        foreach ($this->getUserAddresses() as $ua) {
            $ua->setIsDefault(false);
        }
        $userAddress->setIsDefault(true);
    }

    /**
     * 返回最近的一个ShareSource不包含自己
     *
     * @return ShareSource
     */
    public function getLatestFromShareSource()
    {
        $criteria = Criteria::create()
            ->orderBy(array("id" => Criteria::DESC));

        $fromShareSourceUsers = $this->shareSourceUsers->matching($criteria);

        $filteredShareSourceUsers = $fromShareSourceUsers->filter(function (ShareSourceUser $shareSourceUser) {
            return $shareSourceUser->getShareSource()->getUser() != $this;
        });

        if (!$filteredShareSourceUsers->isEmpty()) {
            return $filteredShareSourceUsers->first()->getShareSource();
        }

        return null;
    }

    public function addFromShareSourceUser(ShareSourceUser $shareSourceUser)
    {
        $this->shareSourceUsers->add($shareSourceUser);
    }

    public function getPendingTotalRewards()
    {
        return $this->pendingTotalRewards;
    }

    public function setPendingTotalRewards($pendingTotalRewards): self
    {
        $this->pendingTotalRewards = $pendingTotalRewards;

        return $this;
    }

    /**
     * @param $amount
     * @return User
     */
    public function increasePendingTotalRewards($amount)
    {
        $this->pendingTotalRewards += $amount;
        return $this;
    }

    /**
     * @param $amount
     * @return User
     */
    public function increaseTotalRewards($amount)
    {
        $this->totalRewards += $amount;
        return $this;
    }

    /**
     * @return Collection|CommandMessage[]
     */
    public function getCommandMessages(): Collection
    {
        return $this->userCommands;
    }

    /**
     * @param CommandMessage $commandMessage
     * @return CommandMessage|null
     */
    public function getCommandMessage(CommandMessage $commandMessage)
    {
        foreach ($this->getCommandMessages() as $cm) {
            if ($commandMessage->getId() == $cm->getId() and $commandMessage->getCommandClass() == $commandMessage->getCommandClass()) {
                return $commandMessage;
            }
        }
        return null;
    }

    public function addUserCommand(CommandMessage $commandMessage): self
    {
        if (!$this->userCommands->contains($commandMessage)) {
            $commandMessage->setUser($this);
            $this->userCommands[] = $commandMessage;
        }

        return $this;
    }

    public function removeUserCommand(CommandMessage $commandMessage): self
    {
        if ($this->userCommands->contains($commandMessage)) {
            $this->userCommands->removeElement($commandMessage);
            // set the owning side to null (unless already changed)
            if ($commandMessage->getUser() === $this) {
                $commandMessage->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserLevel(): ?string
    {
        return $this->userLevel;
    }

    /**
     * @return string
     */
    public function getUserLevelText(): string
    {
        if ($this->getUserLevel()) {
            return UserLevel::$userLevelTextArray[$this->getUserLevel()];
        }

        return "";
    }

    /**
     * @return string
     */
    public function getBianxianUserLevelText(): string
    {
        if ($this->getUserLevel()) {
            return BianxianUserLevel::$userLevelTextArray[$this->getBianxianUserLevel()];
        }

        return "";
    }

    /**
     * @param string $userLevel
     */
    public function setUserLevel(string $userLevel): void
    {
        $this->userLevel = $userLevel;
    }

    /**
     * @return float|null
     */
    public function getUserAccountTotal(): ?float
    {
        return $this->userAccountTotal;
    }

    /**
     * @param float|null $userAccountTotal
     */
    public function setUserAccountTotal(float $userAccountTotal): void
    {
        $this->userAccountTotal = $userAccountTotal;
    }

    /**
     * @return int|null
     */
    public function getRecommandStock(): ?int
    {
        return $this->recommandStock;
    }

    /**
     * @param float|null $recommandStock
     */
    public function setRecommandStock(float $recommandStock): void
    {
        $this->recommandStock = $recommandStock;
    }

    /**
     * @return null|Teacher
     */
    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    /**
     * @param Teacher $teacher
     */
    public function setTeacher(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return UserAccountOrder[]|Collection
     */
    public function getUserAccountOrders()
    {
        return $this->userAccountOrders;
    }

    /**
     * @return int
     */
    public function getTotalUserAccountOrders() {
        return $this->userAccountOrders->count();
    }

    /**
     * @return ArrayCollection|UserAccountOrder[]
     */
    public function getUserAccountOrdersAsRecommander()
    {
        $userAccountOrderAsRecommanders = new ArrayCollection();
        foreach ($this->userAccountOrders as $userAccountOrder) {
            if ($userAccountOrder->isRecommandRewards() and $userAccountOrder->isPaid()) {
                $userAccountOrderAsRecommanders->add($userAccountOrder);
            }
        }
        return $userAccountOrderAsRecommanders;
    }

    /**
     * @return int
     */
    public function getTotalUserAccountOrdersAsRecommander() {
        return $this->getUserAccountOrdersAsRecommander()->count();
    }

    /**
     * @param UserAccountOrder[]|ArrayCollection $userAccountOrders
     */
    public function setUserAccountOrders($userAccountOrders): void
    {
        $this->userAccountOrders = $userAccountOrders;
    }

    /**
     * @return UpgradeUserOrder[]|Collection
     */
    public function getUpgradeUserOrders()
    {
        return $this->upgradeUserOrders;
    }

    /**
     * @return UpgradeUserOrder | null
     */
    public function getLatestUpgradeUserOrder()
    {
        foreach ($this->getUpgradeUserOrders() as $upgradeUserOrder) {
            return $upgradeUserOrder;
        }
        return null;
    }

    /**
     * @param UpgradeUserOrder[] $upgradeUserOrders
     */
    public function setUpgradeUserOrders($upgradeUserOrders): void
    {
        $this->upgradeUserOrders = $upgradeUserOrders;
    }

    /**
     * @param Course $course
     * @return CourseOrder
     */
    public function createCourseOrder(Course $course)
    {
        $courseOrder = GroupUserOrder::factory($this, $course->getProduct());
        $this->groupUserOrders->add($courseOrder);
        return $courseOrder;
    }

    /**
     * @param int $timestamp
     */
    public function setLastLoginTimestamp(int $timestamp) {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);
        $this->setLastLogin($dateTime);
    }


    /**
     * 创建升级会员订单
     * @param $upgradeUserOrderType
     * @param $newUserLevel
     * @param GroupUserOrder|null $groupUserOrder
     * @return UpgradeUserOrder|null
     */
    public function createUpgradeUserOrder($upgradeUserOrderType, $newUserLevel, ?GroupUserOrder $groupUserOrder)
    {
        $isCreateUpgradeUserOrder = false;
        $oldUserLevel = $this->getUserLevel();
        $oldBianxianUserLevel = $this->getBianxianUserLevel();

        $this->info('user ' . $this->getId() . ' is trying to upgrade from ' . $oldUserLevel . ' to ' . $newUserLevel);

        if ($upgradeUserOrderType == UpgradeUserOrder::JINQIU and
            UserLevel::$userLevelPriorityArray[$newUserLevel] > UserLevel::$userLevelPriorityArray[$oldUserLevel]) {
            $isCreateUpgradeUserOrder = true;
        } else if ($upgradeUserOrderType == UpgradeUserOrder::BIANXIAN and
            BianxianUserLevel::$userLevelPriorityArray[$newUserLevel] > BianxianUserLevel::$userLevelPriorityArray[$oldBianxianUserLevel]) {
            $isCreateUpgradeUserOrder = true;
        }

        if (!$isCreateUpgradeUserOrder){
            $this->info('user ' . $this->getId() . ' should not upgrade user level');
            return null;
        }

        $upgradeUserOrder = UpgradeUserOrder::factory($upgradeUserOrderType, $this, $newUserLevel, $groupUserOrder);
        $upgradeUserOrder->setRecommanderUser($this->getParentUser());
        if ($upgradeUserOrder->isJinqiu()) {
            $upgradeUserOrder->setPartnerUser($this->getTopParentPartnerUser());
        }
        else {
            $upgradeUserOrder->setPartnerUser($this->getBianxianTopParentPartnerUser());
        }

        if ($groupUserOrder and $groupUserOrder->isCourseOrder()) {
            $upgradeUserOrder->setPartnerTeacherUser($groupUserOrder->getProduct()->getCourse()->getTeacher()->getUser());
        }

        $this->upgradeUserOrders->add($upgradeUserOrder);
        return $upgradeUserOrder;
    }

    /**
     * 创建推荐人佣金订单或提现订单
     * @param string $userAccountOrderType
     * @param float $amount
     * @param UpgradeUserOrder|null $upgradeUserOrder
     * @param Course|null $course
     * @param string|null $memo
     * @return UserAccountOrder
     */
    public function createUserAccountOrder($userAccountOrderType, $amount, UpgradeUserOrder $upgradeUserOrder = null, Course $course = null, $memo = null)
    {
        $userAccountOrder = UserAccountOrder::factory($this, $userAccountOrderType, $amount, $upgradeUserOrder, $course, $memo);
        $this->userAccountOrders->add($userAccountOrder);
        return $userAccountOrder;
    }

    /**
     * 创建提现订单
     * @param $amount
     * @return UserAccountOrder
     */
    public function createWithdrawUserAccountOrder($amount)
    {
        return $this->createUserAccountOrder(UserAccountOrder::WITHDRAW, $amount, null, null);
    }

    /**
     * 增加推荐名额
     * @param $qty
     */
    public function increaseRecommandStock($qty)
    {
        $this->recommandStock += $qty;
    }

    /**
     * 增加用户账户钱
     * @param $amount
     */
    public function increaseUserAccountTotal($amount)
    {
        $this->userAccountTotal += $amount;
    }

    /**
     * 减少用户账户钱
     * @param $amount
     */
    public function decreaseUserAccountTotal($amount)
    {
        $this->userAccountTotal -= $amount;
    }

    /**
     * @param $userLevel
     */
    public function upgradeUserLevel($userLevel)
    {
        $this->setUserLevel($userLevel);
        $this->setUpdatedAt();
    }

    /**
     * @param $bianxianUserLevel
     */
    public function upgradeBianxianUserLevel($bianxianUserLevel)
    {
        $this->setBianxianUserLevel($bianxianUserLevel);
        $this->setUpdatedAt();
    }

    /**
     * 返回最近一次上的输入的课，如果为空则返回最近一次的课
     *
     * @param string $subject
     * @return Course | null
     */
    public function getLatestCourse($subject = null)
    {
        $course = null;

        if ($subject == null) {
            foreach ($this->courseStudents as $courseStudent) {
                if ($courseStudent->getStudentUser() == $this) {
                    return $courseStudent->getCourse();
                }
            }
        } else {
            foreach ($this->courseStudents as $courseStudent) {
                if ($courseStudent->getStudentUser() == $this and $courseStudent->getCourse()->getSubject() == $subject) {
                    return $courseStudent->getCourse();
                }
            }
        }

        return $course;
    }

    /**
     * 已经提现的总数
     * @return float|int
     */
    public function getWithDrawedTotal()
    {
        $withdrawTotal = 0;
        foreach ($this->getUserAccountOrders() as $userAccountOrder) {
            if ($userAccountOrder->isWithdraw() and $userAccountOrder->isPaid()) {
                $withdrawTotal += $userAccountOrder->getAmount();
            }
        }
        return $withdrawTotal;
    }

    /**
     * 已经提现的总数
     * @return float|int
     */
    public function getWithDrawingTotal()
    {
        $withdrawingTotal = 0;
        foreach ($this->getUserAccountOrders() as $userAccountOrder) {
            if ($userAccountOrder->isWithdraw() and !$userAccountOrder->isPaid()) {
                $withdrawingTotal += $userAccountOrder->getAmount();
            }
        }
        return $withdrawingTotal;
    }

    /**
     * @return null|string
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param null|string $company
     */
    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return null|string
     */
    public function getIdNum(): ?string
    {
        return $this->idNum;
    }

    /**
     * @param null|string $idNum
     */
    public function setIdNum(?string $idNum): void
    {
        $this->idNum = $idNum;
    }

    /**
     * @return null|string
     */
    public function getWechat(): ?string
    {
        return $this->wechat;
    }

    /**
     * @param null|string $wechat
     */
    public function setWechat(?string $wechat): void
    {
        $this->wechat = $wechat;
    }

    /**
     * @return bool
     */
    public function isCompletedPersonalInfo()
    {
        return $this->getName() and $this->getIdNum() and $this->getPhone();
    }

    /**
     * @return null|string
     */
    public function getRecommanderName(): ?string
    {
        return $this->recommanderName;
    }

    /**
     * @param null|string $recommanderName
     */
    public function setRecommanderName(?string $recommanderName): void
    {
        $this->recommanderName = $recommanderName;
    }

    /**
     * 是否已锁定推荐人
     * @return bool
     */
    public function isRecommanderVerified()
    {
        return $this->getParentUser() != null;
    }

    /**
     * @return int|null
     */
    public function getParentUserExpiresAt(): ?int
    {
        return $this->parentUserExpiresAt;
    }

    /**
     * @param int|null $parentUserExpiresAt
     */
    public function setParentUserExpiresAt(?int $parentUserExpiresAt): void
    {
        $this->parentUserExpiresAt = $parentUserExpiresAt;
    }

    /**
     * @param int $qty
     * @param UpgradeUserOrder $upgradeUserOrder
     * @param string $memo
     * @return UserRecommandStockOrder
     */
    public function createUserRecommandStockOrder($qty, UpgradeUserOrder $upgradeUserOrder = null, $memo = null) {
        $userRecommandStockOrder = UserRecommandStockOrder::factory($this, $qty, $upgradeUserOrder, $memo);
        $this->getUserRecommandStockOrders()->add($userRecommandStockOrder);
        return $userRecommandStockOrder;
    }

    /**
     * @return UserRecommandStockOrder[]|Collection
     */
    public function getUserRecommandStockOrders()
    {
        return $this->userRecommandStockOrders;
    }

    /**
     * @return int
     */
    public function getTotalRecommandStockOrders() {
        return $this->getUserRecommandStockOrders()->count();
    }

    /**
     * 用掉的名额
     * @return int
     */
    public function getUsedRecommandStockCount() {
        $use = 0;
        foreach ($this->getUserRecommandStockOrders() as $k => $v) {
            if( $v->getQty() < 0 ){
                $use++;
            }
        }
        return $use;
    }

    /**
     * @param UserRecommandStockOrder $userRecommandStockOrder
     */
    public function addUserRecommandStockOrder(UserRecommandStockOrder $userRecommandStockOrder) {
        $userRecommandStockOrder->setUser($this);
        $this->getUserRecommandStockOrders()->add($userRecommandStockOrder);
    }

    /**
     * @param UserRecommandStockOrder[]|Collection $userRecommandStockOrders
     */
    public function setUserRecommandStockOrders($userRecommandStockOrders): void
    {
        $this->userRecommandStockOrders = $userRecommandStockOrders;
    }

    /**
     * @return Product[]|ArrayCollection
     */
    public function getSupplierProducts()
    {
        return $this->supplierProducts;
    }

    /**
     * @param Product[]|ArrayCollection $supplierProducts
     */
    public function setSupplierProducts($supplierProducts): void
    {
        $this->supplierProducts = $supplierProducts;
    }

    /**
     * @return User|null
     */
    public function getTeacherRecommanderUser(): ?User
    {
        return $this->teacherRecommanderUser;
    }

    /**
     * @param User|null $teacherRecommanderUser
     */
    public function setTeacherRecommanderUser(?User $teacherRecommanderUser): void
    {
        $this->teacherRecommanderUser = $teacherRecommanderUser;
    }

    /**
     * @return User|null
     */
    public function getPartnerTeacherRecommanderUser(): ?User
    {
        return $this->partnerTeacherRecommanderUser;
    }

    /**
     * @param User|null $partnerTeacherRecommanderUser
     */
    public function setPartnerTeacherRecommanderUser(?User $partnerTeacherRecommanderUser): void
    {
        $this->partnerTeacherRecommanderUser = $partnerTeacherRecommanderUser;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getRecommandStudentUsers()
    {
        return $this->recommandStudentUsers;
    }

    /**
     * @param User[]|ArrayCollection $recommandStudentUsers
     */
    public function setRecommandStudentUsers($recommandStudentUsers): void
    {
        $this->recommandStudentUsers = $recommandStudentUsers;
    }

    /**
     * 返回最上线的一个高级用户
     * @return User|null
     */
    public function getTopParentAdvancedUser() {

        //如果自己就是partner则就是自己
        if ($this->isAdvancedUser()) {
            return $this;
        }

        if ($this->getParentUser() == null) {
            return null;
        }

        $user = $this;
        while ($parent = $user->getParentUser()) {
            if ($parent->isAdvancedUser()) {
                return $parent;
            }
            $user = $parent;
        }
        return null;
    }

    /**
     * 返回最上线的一个合伙人
     * @return User|null
     */
    public function getTopParentPartnerUser() {

        //如果自己就是partner则就是自己
        if ($this->isPartnerUser()) {
            return $this;
        }

        if ($this->getParentUser() == null) {
            return null;
        }

        $user = $this;
        while ($parent = $user->getParentUser()) {
            if ($parent == null) {
                return null;
            }

            if ($parent->isPartnerUser()) {
                return $parent;
            }
            $user = $parent;
        }
        return null;
    }

    /**
     * 返回变现最上线的一个高级用户
     * @return User|null
     */
    public function getBianxianTopParentAdvancedUser() {

        //如果自己就是partner则就是自己
        if ($this->isBianxianAdvancedUser()) {
            return $this;
        }

        if ($this->getParentUser() == null) {
            return null;
        }

        $user = $this;
        while ($parent = $user->getParentUser()) {
            if ($parent == null) {
                return null;
            }

            if ($parent->isBianxianAdvancedUser()) {
                return $parent;
            }
            $user = $parent;
        }
        return null;
    }

    /**
     * 返回变现最上线的一个合伙人
     * @return User|null
     */
    public function getBianxianTopParentPartnerUser() {

        //如果自己就是partner则就是自己
        if ($this->isBianxianPartnerUser()) {
            return $this;
        }

        if ($this->getParentUser() == null) {
            return null;
        }

        $user = $this;
        while ($parent = $user->getParentUser()) {
            if ($this->getParentUser() == null) {
                return null;
            }

            if ($parent->isBianxianPartnerUser()) {
                return $parent;
            }
            $user = $parent;
        }
        return null;
    }

    /**
     * 返回变现最上线的一个合伙人|分院
     * @return User|null
     */
    public function getBianxianTopParentPartnerUpUser() {

        //如果自己就是partner则就是自己
        if ($this->isBianxianPartnerUpUser()) {
            return $this;
        }

        if ($this->getParentUser() == null) {
            return null;
        }

        $user = $this;
        while ($parent = $user->getParentUser()) {
            if ($this->getParentUser() == null) {
                return null;
            }

            if ($parent->isBianxianPartnerUpUser()) {
                return $parent;
            }
            $user = $parent;
        }
        return null;
    }

    /**
     * @return UserParentLog[] | ArrayCollection
     */
    public function getUserParentLogs()
    {
        return $this->userParentLogs;
    }

    /**
     * @param UserParentLog[]|ArrayCollection $userParentLogs
     */
    public function setUserParentLogs($userParentLogs): void
    {
        $this->userParentLogs = $userParentLogs;
    }

    /**
     * @param UserParentLog $userParentLog
     */
    public function addUserParentLog(UserParentLog $userParentLog) {
        $this->userParentLogs->add($userParentLog);
    }

    /**
     * @return UserParentLog[]|ArrayCollection
     */
    public function getUserLogs()
    {
        return $this->userLogs;
    }

    /**
     * @param UserParentLog[]|ArrayCollection $userLogs
     */
    public function setUserLogs($userLogs): void
    {
        $this->userLogs = $userLogs;
    }

    /**
     * @param $log
     */
    public function info($log) {
        $userLog = UserLog::factory($this, UserLog::INFO, $log, json_encode(debug_backtrace()));
        $this->getUserLogs()->add($userLog);
    }

    /**
     * @param $log
     */
    public function error($log) {
        $userLog = UserLog::factory($this, UserLog::ERROR, $log, json_encode(debug_backtrace()));
        $this->getUserLogs()->add($userLog);
    }

    /**
     * 合并姓名和昵称
     * @return string
     */
    public function getDisplayName() {
        return $this->getNickname() . '('. $this->getName() . ')';
    }

    public function __toString()
    {
        return 'ID: ' . $this->getId()
            . ' 名称: ' . $this->getDisplayName()
            . ' 等级: ' . $this->getUserLevelText() . '|' . $this->getBianxianUserLevelText();

    }

    /**
     * @return null|string
     */
    public function getBianxianUserLevel(): ?string
    {
        return $this->bianxianUserLevel;
    }

    /**
     * @param null|string $bianxianUserLevel
     */
    public function setBianxianUserLevel($bianxianUserLevel): void
    {
        $this->bianxianUserLevel = $bianxianUserLevel;
    }

    /**
     * @return null|string
     */
    public function getBank(): ?string
    {
        return $this->bank;
    }

    /**
     * @param null|string $bank
     */
    public function setBank(?string $bank): void
    {
        $this->bank = $bank;
    }

    /**
     * @return null|string
     */
    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    /**
     * @param null|string $bankAccountNumber
     */
    public function setBankAccountNumber(?string $bankAccountNumber): void
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }

    /**
     * @return null|string
     */
    public function getBankAccountName(): ?string
    {
        return $this->bankAccountName;
    }

    /**
     * @param null|string $bankAccountName
     */
    public function setBankAccountName(?string $bankAccountName): void
    {
        $this->bankAccountName = $bankAccountName;
    }

    public function isSupplier() {
        return UserLevel::SUPPLIER == $this->getUserLevel();
    }


    /**
     * 用户是否有推荐资格
     * @return bool
     */
    public function hasRecommandRight() {
        return
            $this->getUserLevel() == UserLevel::ADVANCED
            or $this->getUserLevel() == UserLevel::ADVANCED2
            or $this->getUserLevel() == UserLevel::ADVANCED3
            or $this->getUserLevel() == UserLevel::PARTNER
            or $this->getBianxianUserLevel() == BianxianUserLevel::ADVANCED
            or $this->getBianxianUserLevel() == BianxianUserLevel::PARTNER
            or $this->getBianxianUserLevel() == BianxianUserLevel::DISTRIBUTOR;
    }

    /**
     * @return bool
     */
    public function isSecurity() {
        return $this->hasRole(self::ROLE_SECURITY);
    }

    /**
     * @return null|string
     */
    public function getDisplayRecommanderName() {
//        if ($this->getParentUser()) {
//            return $this->getParentUser()->getDisplayName();
//        }

        return $this->getRecommanderName() != null ? $this->getRecommanderName() : '佐商学社';
    }

    /*
     * 当前用户等级是否大于等于高级VIP用户
     * @return bool
     */
    public function isAdavancePlusUserLevel() {
        return UserLevel::$userLevelPriorityArray[$this->getUserLevel()] >= UserLevel::$userLevelPriorityArray[UserLevel::ADVANCED];
    }

    /**
     * 是否有上系统课权限
     * @param bool $isCheckRealPersonInfo 是否需要验证系统学院实名
     * @return bool
     * @author zxqc2018
     */
    public function isSystemSubjectPrivilege($isCheckRealPersonInfo = true)
    {
        return in_array($this->bianxianUserLevel, [BianxianUserLevel::PARTNER, BianxianUserLevel::DISTRIBUTOR]) || $this->isBianxianAdvancedUser() && (!$isCheckRealPersonInfo || $isCheckRealPersonInfo && $this->isCompletedPersonalInfo());
    }
    /*
     * 成为系统学院时间
     * @return bool
     */
    public function getAdvancedTime() {
        $advancedTime = '';
        foreach ($this->getUpgradeUserOrders() as $upgradeUserOrder) {
            if( $upgradeUserOrder->getUserLevel() == BianxianUserLevel::ADVANCED ){
                $advancedTime = $upgradeUserOrder->getCreatedAt();
            }
        }
        return $advancedTime;
    }

    /**
     * @return ArrayCollection|Collection
     * @author zxqc2018
     */
    public function getMyUnlockCategory()
    {
        $unlockCategoryList = new ArrayCollection();
        $criteria = Criteria::create()
            ->orderBy(array("id" => Criteria::DESC));

        $groupUserOrders = $this->groupUserOrders->matching($criteria);

        $filteredGroupUserOrders = $groupUserOrders->filter(function (GroupUserOrder $groupUserOrder) {
            return !empty($groupUserOrder->getUnlockCategory()) && $groupUserOrder->isPaid();
        });

        if (!$filteredGroupUserOrders->isEmpty()) {
            foreach ($filteredGroupUserOrders as $item) {
                $unlockCategoryList->add($item->getUnlockCategory());
            }
        }
        return $unlockCategoryList;
    }

    /**
     * 取得课程最新的一张集call订单
     * @param Product $product
     * @param bool $isCallOrder
     * @return GroupUserOrder|null
     * @author zxqc2018
     */
    public function getNewestGroupUserOrder(Product $product, $isCallOrder = null)
    {
        $res = null;
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('product', $product))
            ->orderBy(array("id" => Criteria::DESC));

        $groupUserOrders = $this->groupUserOrders->matching($criteria);


        if (!is_null($isCallOrder)) {
            $filteredGroupUserOrders = $groupUserOrders->filter(function (GroupUserOrder $groupUserOrder) use ($isCallOrder) {
                return $isCallOrder ? $groupUserOrder->isGroupOrder() : !$groupUserOrder->isGroupOrder();
            });
        } else {
            $filteredGroupUserOrders = $groupUserOrders;
        }

        if (!$filteredGroupUserOrders->isEmpty()) {
            /**
             * @var GroupUserOrder $res;
             */
            $res = $filteredGroupUserOrders->first();
        }
        return $res;
    }

    
    //todo
    // 获取最后一个 course_inspector
    public function getShowInspector() {
        $inspector = FactoryUtil::courseInspectorRepository()->findOneBy(['user'=>$this],['inspectorEndDate'=>'desc']);
        if( $inspector->getInspectorStartDate() < time() && $inspector->getInspectorEndDate() >= time()  ){
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        $recommanderArray = [];
        if ($this->getParentUser() != null) {
            $recommanderArray = [
                'id' => $this->getParentUser()->getId(),
                'avatarUrl' => $this->getParentUser()->getAvatarUrl(),
                'nickname' => $this->getParentUser()->getNickname(),
                'name' => $this->getParentUser()->getName(),
                'phone' => $this->getParentUser()->getPhone(),
                'company' => $this->getParentUser()->getCompany()
            ];
        }

        return [
            'id' => $this->getId(),
            'nickname' => $this->getNickname(),
            'userLevel' => $this->userLevel ? $this->userLevel : null,
            'userLevelText' => $this->getUserLevel() ? UserLevel::$userLevelTextArray[$this->getUserLevel()] : null,
            'bianxianUserLevel' => $this->bianxianUserLevel ? $this->bianxianUserLevel : null,
            'bianxianUserLevelText' => $this->bianxianUserLevel ? BianxianUserLevel::$userLevelTextArray[$this->getBianxianUserLevel()] : null,
            'userAccountTotal' => $this->getUserAccountTotal(),
            'userRecommandStock' => $this->getRecommandStock(),
            'avatarUrl' => $this->getAvatarUrl(),
            'totalRewards' => $this->getTotalRewards(),
            'defaultAddress' => $this->getDefaultUserAddress() != null ? $this->getDefaultUserAddress()->getArray() : null,
            'lastLogin' => $this->getLastLogin(),
            'recommander' => $recommanderArray,
            'recommanderUserName' => $recommanderArray?$recommanderArray['nickname']:'',
            'isTeacher' => $this->isTeacher(),
            'isCompletedPersonalInfo' => $this->isCompletedPersonalInfo(),
            'name' => $this->getName(),
            'company' => $this->getCompany(),
            'phone' => $this->getPhone(),
            'idNum' => $this->getIdNum(),
            'wechat' => $this->getWechat(),
            'wxUnionId' => $this->getWxUnionId(),
            'recommanderName' => $this->getDisplayRecommanderName(),
            'totalStudents' => $this->getTeacher() ? $this->getTeacher()->getTotalStudentUsers() : 0,
            'totalShares' => $this->getTotalSharedUsers(),
            'bank' => $this->getBank(),
            'bankAccountNumber' => $this->getBankAccountNumber(),
            'bankAccountName' => $this->getBankAccountName(),
            'isSupplier' => !$this->getSupplierProducts()->isEmpty(),
            'isPartner' => $this->getUserLevel()== UserLevel::PARTNER?true:false,
            'recommandNameEditable' => $this->getParentUser() == null ? true : false,
            'isAdvancedPlus' => $this->isAdavancePlusUserLevel(),
            'advancedTime' => $this->getAdvancedTime(),
            'isNewUser' => $this->isNewUser(),
            "showInspector" =>$this->getShowInspector(),
        ];
    }

    /**
     * @return array
     */
    public function getLittleArray(): array
    {
        return [
            'id' => $this->getId(),
            'nickname' => $this->getNickname(),
            'phone' => $this->getPhone(),
        ];
    }
}
