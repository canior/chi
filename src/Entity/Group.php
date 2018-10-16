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
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 */
class Group implements Dao
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupBuying")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupOrder", mappedBy="group")
     */
    private $groupOrders;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->setPending();
        $this->groupOrders = new ArrayCollection();
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
     * @return Collection|GroupOrder[]
     */
    public function getGroupOrders(): Collection
    {
        return $this->groupOrders;
    }

    public function addGroupOrder(GroupOrder $groupOrder): self
    {
        if (!$this->groupOrders->contains($groupOrder)) {
            $this->groupOrders[] = $groupOrder;
            $groupOrder->setParentGroup($this);
        }

        return $this;
    }

    public function removeGroupOrder(GroupOrder $groupOrder): self
    {
        if ($this->groupOrders->contains($groupOrder)) {
            $this->groupOrders->removeElement($groupOrder);
            // set the owning side to null (unless already changed)
            if ($groupOrder->getParentGroup() === $this) {
                $groupOrder->setParentGroup(null);
            }
        }

        return $this;
    }
}
