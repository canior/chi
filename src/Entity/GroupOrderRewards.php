<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupOrderRewardsRepository")
 */
class GroupOrderRewards implements Dao
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupOrder", inversedBy="groupOrderRewards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $userRewards;

    /**
     * GroupOrderRewards constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
    }

    public function getGroupOrder(): ?GroupOrder
    {
        return $this->groupOrder;
    }

    public function setGroupOrder(?GroupOrder $groupOrder): self
    {
        $this->groupOrder = $groupOrder;

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
