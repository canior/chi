<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupUserOrderRewardsRepository")
 */
class GroupUserOrderRewards implements Dao
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupUserOrder", inversedBy="groupUserOrderRewards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupUserOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupUserOrderRewards")
     */
    private $user;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $userRewards;

    /**
     * GroupUserOrderRewards constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
    }

    public function getGroupUserOrder(): ?GroupUserOrder
    {
        return $this->groupUserOrder;
    }

    public function setGroupUserOrder(?GroupUserOrder $groupUserOrder): self
    {
        $this->groupUserOrder = $groupUserOrder;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserRewards()
    {
        return $this->userRewards;
    }

    public function setUserRewards($userRewards): self
    {
        $this->userRewards = $userRewards;

        return $this;
    }
}
