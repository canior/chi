<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PaymentStatusTrait;
use App\Entity\Traits\StatusTrait;
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
        StatusTrait,
        PaymentStatusTrait,
        CreatedAtTrait,
        UpdatedAtTrait;


    const CREATED = 'created';
    const PENDING = 'pending';
    const SHIPPING = 'shipping';
    const DELIVERED = 'delivered';
    const RETURNING = 'returning';
    const RMA_RECEIVED = 'rma_received';

    public static $statuses = [
        self::CREATED => '已创建',
        self::PENDING => '待发货',
        self::SHIPPING => '发货中',
        self::DELIVERED => '已收货',
        self::RETURNING => '退货中',
        self::RMA_RECEIVED => '收到退货'
    ];

    const PAID = 'paid';
    const UNPAID = 'unpaid';
    const REFUND = 'refund';

    public static $paymentStatuses = [
        self::PAID => '已支付',
        self::UNPAID => '未支付',
        self::REFUND => '已退款',
    ];


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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $carrierName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trackingNo;

    /**
     * @ORM\OneToMany(targetEntity="GroupUserOrderRewards", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $groupUserOrderRewards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReview", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $productReviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrderLog", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $groupUserOrderLogs;

    /**
     * GroupUserOrder constructor.
     */
    public function __construct()
    {
        $this->setCreated();
        $this->setUnPaid();
        $this->setTotal(0);
        $this->setCreatedAt(time());
        $this->setUpdatedAt(time());
        $this->groupUserOrderRewards = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->groupUserOrderLogs = new ArrayCollection();
    }

    public function setCreated() : self {
        $this->status = self::CREATED;
        return $this;
    }

    public function isCreated() : bool {
        return self::CREATED == $this->getStatus();
    }

    public function setPending() : self {
        $this->status = self::PENDING;
        return $this;
    }

    public function isPending() : bool {
        return self::PENDING == $this->getStatus();
    }

    public function setShipping() : self {
        $this->status = self::SHIPPING;
        return $this;
    }

    public function isShipping() : bool {
        return self::SHIPPING == $this->getStatus();
    }

    public function setDelivered() : self {
        $this->status = self::DELIVERED;
        return $this;
    }

    public function isDelivered() : bool {
        return self::DELIVERED == $this->getStatus();
    }

    public function setReturning() : self {
        $this->status = self::RETURNING;
        return $this;
    }

    public function isReturning() : bool {
        return self::RETURNING == $this->getStatus();
    }

    public function setRmaReceived() : self {
        $this->status = self::RMA_RECEIVED;
        return $this;
    }

    public function isRmaReceived() : bool {
        return self::RMA_RECEIVED == $this->getStatus();
    }

    public function setPaid() : self {
        $this->paymentStatus = self::PAID;
        return $this;
    }

    public function isPaid() : bool {
        return self::PAID == $this->getPaymentStatus();
    }


    public function setUnPaid() : self {
        $this->paymentStatus = self::UNPAID;
        return $this;
    }

    public function isUnPaid() : bool {
        return self::UNPAID == $this->getPaymentStatus();
    }

    public function setRefund() : self {
        $this->paymentStatus = self::UNPAID;
        return $this;
    }

    public function isRefund() : bool {
        return self::REFUND == $this->getPaymentStatus();
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

    public function getTotal() : float
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

    public function setCarrierName(?string $carrierName): self
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

    /**
     * @return User
     */
    public function getUser() : User {
        return $this->getUserAddress()->getUser();
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
            $groupUserOrderLog->setGroupUserOrder($this);
        }

        return $this;
    }

    public function removeGroupUserOrderLog(GroupUserOrderLog $groupUserOrderLog): self
    {
        if ($this->groupUserOrderLogs->contains($groupUserOrderLog)) {
            $this->groupUserOrderLogs->removeElement($groupUserOrderLog);
            // set the owning side to null (unless already changed)
            if ($groupUserOrderLog->getGroupUserOrder() === $this) {
                $groupUserOrderLog->setGroupUserOrder(null);
            }
        }

        return $this;
    }
}
