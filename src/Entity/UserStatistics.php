<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserStatisticsRepository")
 */
class UserStatistics
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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

    public function getId()
    {
        return $this->id;
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
}
