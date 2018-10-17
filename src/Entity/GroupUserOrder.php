<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupUserOrderRepository")
 */
class GroupUserOrder implements Dao
{
    use IdTrait,
        CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="GroupOrder", inversedBy="groupUserOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAddress")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userAddress;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $total;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $orderRewards;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $carrierName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trackingNo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDelivered;

    /**
     * @ORM\OneToMany(targetEntity="GroupUserOrderRewards", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $groupUserOrderRewards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReview", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $productReviews;

    /**
     * GroupUserOrder constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->groupUserOrderRewards = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
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

    public function getUserAddress(): ?UserAddress
    {
        return $this->userAddress;
    }

    public function setUserAddress(?UserAddress $userAddress): self
    {
        $this->userAddress = $userAddress;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getOrderRewards()
    {
        return $this->orderRewards;
    }

    public function setOrderRewards($orderRewards): self
    {
        $this->orderRewards = $orderRewards;

        return $this;
    }

    public function getCarrierName(): ?string
    {
        return $this->carrierName;
    }

    public function setCarrierName(string $carrierName): self
    {
        $this->carrierName = $carrierName;

        return $this;
    }

    public function getTrackingNo(): ?string
    {
        return $this->trackingNo;
    }

    public function setTrackingNo(?string $trackingNo): self
    {
        $this->trackingNo = $trackingNo;

        return $this;
    }

    public function getIsDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(bool $isDelivered): self
    {
        $this->isDelivered = $isDelivered;

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
            $groupOrderReward->setGroupUserOrder($this);
        }

        return $this;
    }

    public function removeGroupUserOrderReward(GroupUserOrderRewards $groupOrderReward): self
    {
        if ($this->groupUserOrderRewards->contains($groupOrderReward)) {
            $this->groupUserOrderRewards->removeElement($groupOrderReward);
            // set the owning side to null (unless already changed)
            if ($groupOrderReward->getGroupUserOrder() === $this) {
                $groupOrderReward->setGroupUserOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductReview[]
     */
    public function getProductReviews(): Collection
    {
        return $this->productReviews;
    }

    public function addProductReview(ProductReview $productReview): self
    {
        if (!$this->productReviews->contains($productReview)) {
            $this->productReviews[] = $productReview;
            $productReview->setGroupUserOrder($this);
        }

        return $this;
    }

    public function removeProductReview(ProductReview $productReview): self
    {
        if ($this->productReviews->contains($productReview)) {
            $this->productReviews->removeElement($productReview);
            // set the owning side to null (unless already changed)
            if ($productReview->getGroupUserOrder() === $this) {
                $productReview->setGroupUserOrder(null);
            }
        }

        return $this;
    }
}
