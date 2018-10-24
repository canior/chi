<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserStatisticsRepository")
 */
class UserStatistics
{
    use IdTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="userStatistics", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $childrenNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $sharedNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupOrderNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupUserOrderNum;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $spentTotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $orderRewardsTotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $userRewardsTotal;

    /**
     * UserStatistics constructor.
     */
    public function __construct()
    {
        $this->setChildrenNum(0);
        $this->setSharedNum(0);
        $this->setGroupOrderNum(0);
        $this->setGroupUserOrderNum(0);
        $this->setSpentTotal(0);
        $this->setOrderRewardsTotal(0);
        $this->setUserRewardsTotal(0);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getChildrenNum(): ?int
    {
        return $this->childrenNum;
    }

    public function setChildrenNum(int $childrenNum): self
    {
        $this->childrenNum = $childrenNum;

        return $this;
    }

    public function getSharedNum(): ?int
    {
        return $this->sharedNum;
    }

    public function setSharedNum(int $sharedNum): self
    {
        $this->sharedNum = $sharedNum;

        return $this;
    }

    public function getGroupOrderNum(): ?int
    {
        return $this->groupOrderNum;
    }

    public function setGroupOrderNum(int $groupOrderNum): self
    {
        $this->groupOrderNum = $groupOrderNum;

        return $this;
    }

    public function getGroupUserOrderNum(): ?int
    {
        return $this->groupUserOrderNum;
    }

    public function setGroupUserOrderNum(int $groupUserOrderNum): self
    {
        $this->groupUserOrderNum = $groupUserOrderNum;

        return $this;
    }

    public function getSpentTotal()
    {
        return $this->spentTotal;
    }

    public function setSpentTotal($spentTotal): self
    {
        $this->spentTotal = $spentTotal;

        return $this;
    }

    public function getOrderRewardsTotal()
    {
        return $this->orderRewardsTotal;
    }

    public function setOrderRewardsTotal($orderRewardsTotal): self
    {
        $this->orderRewardsTotal = $orderRewardsTotal;

        return $this;
    }

    public function getUserRewardsTotal()
    {
        return $this->userRewardsTotal;
    }

    public function setUserRewardsTotal($userRewardsTotal): self
    {
        $this->userRewardsTotal = $userRewardsTotal;

        return $this;
    }

    public function getRewardsTotal()
    {
        return $this->orderRewardsTotal + $this->userRewardsTotal;
    }
}
