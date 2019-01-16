<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser implements Dao
{
    const ROLE_CUSTOMER = 'ROLE_CUSTOMER';
    const ROLE_CUSTOMER_SERVICE = 'ROLE_CUSTOMER_SERVICE';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public static $roleTexts = [
        self::ROLE_CUSTOMER => '客户',
        self::ROLE_CUSTOMER_SERVICE => '客服',
        self::ROLE_ADMIN => '管理员',
        self::ROLE_SUPER_ADMIN => '超级管理员'
    ];

    public static $adminRoleTexts = [
        self::ROLE_CUSTOMER_SERVICE => '客服',
        self::ROLE_ADMIN => '管理员',
        self::ROLE_SUPER_ADMIN => '超级管理员'
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subUsers")
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
     * @ORM\OrderBy({"id" = "DESC"})
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
     * @var ArrayCollection $shareSourceUsers
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

        $this->setUserLevel(UserLevel::VISITOR);
        $this->setUserAccountTotal(0);
        $this->setRecommandStock(0);

        $this->upgradeUserOrders = new ArrayCollection();
        $this->userAccountOrders = new ArrayCollection();
        $this->courseStudents = new ArrayCollection();
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isTeacher() {
        return $this->getTeacher() != null;
    }

    /**
     * @return bool
     */
    public function isVisitorUser() {
        return $this->getUserLevel() == UserLevel::VISITOR;
    }

    /**
     * @return bool
     */
    public function hasAdminRoles()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN)
            or $this->hasRole(static::ROLE_ADMIN)
            or $this->hasRole(static::ROLE_CUSTOMER_SERVICE);
    }

    /**
     * @return bool
     */
    public function isAdvancedUser() {
        return $this->getUserLevel() == UserLevel::ADVANCED;
    }

    /**
     * @return bool
     */
    public function isLocked() {
        return !$this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isPartnerUser() {
        return $this->getUserLevel() == UserLevel::PARTNER;
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

    public function getParentUser(): ?self
    {
        return $this->parentUser;
    }

    public function setParentUser(?self $parentUser): self
    {
        if ($parentUser == null) {
            $this->parentUser = null;
            return $this;
        }

        if ($parentUser->getId() != $this->getId())
            $this->parentUser = $parentUser;

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
     * @return int
     */
    public function getTotalChildren() {
        return $this->getSubUsers()->count();
    }

    /**
     * @return int
     */
    public function getTotalRecommandStock() {
        return $this->getRecommandStock() + $this->getTotalChildren();
    }

    public function addSubUser(User $subUser): self
    {
        if (!$this->subUsers->contains($subUser)) {
            $this->subUsers[] = $subUser;
            $subUser->setParentUser($this);
        }

        return $this;
    }

    public function removeSubUser(User $subUser): self
    {
        if ($this->subUsers->contains($subUser)) {
            $this->subUsers->removeElement($subUser);
            // set the owning side to null (unless already changed)
            if ($subUser->getParentUser() === $this) {
                $subUser->setParentUser(null);
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

    public function getActiveUserAddress() : Collection {
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
            'userLevel' => $this->getUserLevel() ? UserLevel::$userLevelTextArray[$this->getUserLevel()] : null,
            'userAccountTotal' => $this->getUserAccountTotal(),
            'userRecommandStock' => $this->getRecommandStock(),
            'avatarUrl' => $this->getAvatarUrl(),
            'totalRewards' => $this->getTotalRewards(),
            'defaultAddress' => $this->getDefaultUserAddress() != null ? $this->getDefaultUserAddress()->getArray() : null,
            'lastLogin' => $this->getLastLogin(),
            'recommander' => $recommanderArray,
            'isTeacher' => $this->isTeacher(),
            'isCompletedPersonalInfo' => $this->isCompletedPersonalInfo(),
            'name' => $this->getName(),
            'company' => $this->getCompany(),
            'phone' => $this->getPhone(),
            'idNum' => $this->getIdNum(),
            'wechat' => $this->getWechat()
        ];
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
    public function getOrCreateTodayUserStatistics() {
        foreach ($this->getUserStatistics() as $userStatistics) {
            if ($userStatistics->isToday()) {
                return $userStatistics;
            }
        }
        $userStatistics =  new UserStatistics($this);
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
    public function setDefaultAddress(UserAddress $userAddress) {
        foreach ($this->getUserAddresses() as $ua) {
            $ua->setIsDefault(false);
        }
        $userAddress->setIsDefault(true);
    }

    /**
     * 返回最近的一个ShareSource
     *
     * @return ShareSource
     */
    public function getLatestFromShareSource() {
        /**
         * @var ShareSourceUser[] $fromShareSources
         */
        $fromShareSourceUsers = $this->shareSourceUsers;

        if (!$fromShareSourceUsers->isEmpty())
            return $fromShareSourceUsers[0]->getShareSource();

        return null;
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
    public function increasePendingTotalRewards($amount) {
        $this->pendingTotalRewards += $amount;
        return $this;
    }

    /**
     * @param $amount
     * @return User
     */
    public function increaseTotalRewards($amount) {
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
    public function getCommandMessage(CommandMessage $commandMessage) {
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
        if ($this->getUserLevel())
            return UserLevel::$userLevelTextArray[$this->userLevel];
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
     * @param UserAccountOrder[] $userAccountOrders
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
    public function getLatestUpgradeUserOrder() {
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
     * 创建升级会员订单
     * @param string $userLevel
     * @return UpgradeUserOrder
     */
    public function createUpgradeUserOrder($userLevel) {
        $upgradeUserOrder = new UpgradeUserOrder($this, $userLevel, UserLevel::$userLevelPriceArray[$userLevel]);
        $this->upgradeUserOrders->add($upgradeUserOrder);
        return $upgradeUserOrder;
    }

    /**
     * 创建推荐人佣金订单或提现订单
     * @param string $userAccountOrderType
     * @param float $amount
     * @param UpgradeUserOrder|null $upgradeUserOrder
     * @return UserAccountOrder
     */
    public function createUserAccountOrder($userAccountOrderType, $amount, UpgradeUserOrder $upgradeUserOrder = null) {
        $userAccountOrder = UserAccountOrder::factory($this, $userAccountOrderType, $amount, $upgradeUserOrder);
        $this->userAccountOrders->add($userAccountOrder);
        return $userAccountOrder;
    }

    /**
     * 增加推荐名额
     * @param $qty
     */
    public function increaseRecommandStock($qty) {
        $this->recommandStock += $qty;
    }

    /**
     * 增加用户账户钱
     * @param $amount
     */
    public function increaseUserAccountTotal($amount) {
        $this->userAccountTotal += $amount;
    }

    /**
     * 升级到合伙人
     * 1. 增加推荐名额
     *
     * @param $userLevel
     */
    public function upgradeUserLevel($userLevel) {
        $this->setUserLevel($userLevel);
        $this->increaseRecommandStock(UserLevel::$userLevelRecommanderStockArray[$userLevel]);
        $this->setUpdatedAt();
    }

    /**
     * 返回最近一次上的输入的课，如果为空则返回最近一次的课
     *
     * @param string $subject
     * @return Course | null
     */
    public function getLatestCourse($subject = null) {
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
    public function getWithDrawedTotal() {
        $withdrawTotal = 0;
        foreach($this->getUserAccountOrders() as $userAccountOrder) {
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
    public function getWithDrawingTotal() {
        $withdrawingTotal = 0;
        foreach($this->getUserAccountOrders() as $userAccountOrder) {
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
    public function isCompletedPersonalInfo() {
        return $this->getName() and $this->getIdNum() and $this->getPhone();
    }

    public function __toString()
    {
       return '用户ID: ' . $this->getId()
           . ' 用户昵称: ' . $this->getNickname()
           . ' 姓名: ' . $this->getName()
           . ' 会员等级: ' . $this->getUserLevelText();
    }

}
