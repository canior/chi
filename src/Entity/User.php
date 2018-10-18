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
    const ROLE_CUSTOMER_SERVICE = 'ROLE_CUSTOMER_SERVICE';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    public static $roleTexts = [
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
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="parentUser", fetch="EXTRA_LAZY")
     */
    private $subUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserShare", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $userShares;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserActivity", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $userActivities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAddress", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $userAddresses;

    /**
     * @ORM\OneToMany(targetEntity="GroupOrder", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $groupOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrderLog", mappedBy="user", fetch="EXTRA_LAZY")
     */
    private $groupUserOrderLogs;

    public function __construct()
    {
        parent::__construct();

        $this->setCreatedAt(time());
        $this->setTotalRewards(0);
        $this->subUsers = new ArrayCollection();
        $this->userShares = new ArrayCollection();
        $this->userActivities = new ArrayCollection();
        $this->userAddresses = new ArrayCollection();
        $this->groupOrders = new ArrayCollection();
        $this->groupUserOrderLogs = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
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
     * @return Collection|UserShare[]
     */
    public function getUserShares(): Collection
    {
        return $this->userShares;
    }

    public function addUserShare(UserShare $userShare): self
    {
        if (!$this->userShares->contains($userShare)) {
            $this->userShares[] = $userShare;
            $userShare->setUser($this);
        }

        return $this;
    }

    public function removeUserShare(UserShare $userShare): self
    {
        if ($this->userShares->contains($userShare)) {
            $this->userShares->removeElement($userShare);
            // set the owning side to null (unless already changed)
            if ($userShare->getUser() === $this) {
                $userShare->setUser(null);
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
     * @return Collection|GroupUserOrderLog[]
     */
    public function getGroupUserOrderLogs(): Collection
    {
        return $this->groupUserOrderLogs;
    }

    public function addGroupUserOrderLog(GroupUserOrderLog $groupUserOrderLog): self
    {
        if (!$this->groupUserOrderLogs->contains($groupUserOrderLog)) {
            $this->groupUserOrderLogs[] = $groupUserOrderLog;
            $groupUserOrderLog->setUser($this);
        }

        return $this;
    }

    public function removeGroupUserOrderLog(GroupUserOrderLog $groupUserOrderLog): self
    {
        if ($this->groupUserOrderLogs->contains($groupUserOrderLog)) {
            $this->groupUserOrderLogs->removeElement($groupUserOrderLog);
            // set the owning side to null (unless already changed)
            if ($groupUserOrderLog->getUser() === $this) {
                $groupUserOrderLog->setUser(null);
            }
        }

        return $this;
    }
}
