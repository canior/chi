<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\ExpiredAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupOrderRepository")
 */
class GroupOrder implements Dao
{
    public static $statuses = [
        self::PENDING => 'pending',
        self::COMPLETED => '已完成'
    ];

    use IdTrait,
        StatusTrait,
        ExpiredAtTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\OneToMany(targetEntity="GroupUserOrder", mappedBy="groupOrder", fetch="EXTRA_LAZY")
     */
    private $groupUserOrders;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->setPending();
        $this->groupUserOrders = new ArrayCollection();
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function setPending(): self
    {
        $this->status = self::PENDING;

        return $this;
    }

    public function setCompleted(): self
    {
        $this->status = self::COMPLETED;

        return $this;
    }

    /**
     * @return Collection|GroupUserOrder[]
     */
    public function getGroupUserOrders(): Collection
    {
        return $this->groupUserOrders;
    }

    public function addGroupUserOrder(GroupUserOrder $groupOrder): self
    {
        if (!$this->groupUserOrders->contains($groupOrder)) {
            $this->groupUserOrders[] = $groupOrder;
            $groupOrder->setGroupOrder($this);
        }

        return $this;
    }

    public function removeGroupUserOrder(GroupUserOrder $groupOrder): self
    {
        if ($this->groupUserOrders->contains($groupOrder)) {
            $this->groupUserOrders->removeElement($groupOrder);
            // set the owning side to null (unless already changed)
            if ($groupOrder->getGroupOrder() === $this) {
                $groupOrder->setGroupOrder(null);
            }
        }

        return $this;
    }
}
