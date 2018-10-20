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
    const CANCELLED = 'cancelled';
    const PENDING = 'pending';
    const SHIPPING = 'shipping';
    const DELIVERED = 'delivered';
    const RETURNING = 'returning';
    const RMA_RECEIVED = 'rma_received';

    public static $statuses = [
        self::CREATED => '已创建',
        self::CANCELLED => '已取消',
        self::PENDING => '待发货',
        self::SHIPPING => '发货中',
        self::DELIVERED => '已收货',
        self::RETURNING => '退货中',
        self::RMA_RECEIVED => '收到退货'
    ];

    const PAID = 'paid';
    const UNPAID = 'unpaid';
    const REFUNDING = 'refunding';
    const REFUNDED = 'refunded';

    public static $paymentStatuses = [
        self::PAID => '已支付',
        self::UNPAID => '未支付',
        self::REFUNDING => '退款中',
        self::REFUNDED => '已退款'
    ];


    /**
     * @ORM\ManyToOne(targetEntity="GroupOrder", inversedBy="groupUserOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAddress")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userAddress;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $prePayId;

    /**
     * @ORM\OneToMany(targetEntity="GroupUserOrderRewards", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $groupUserOrderRewards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReview", mappedBy="groupUserOrder", fetch="EXTRA_LAZY")
     */
    private $productReviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrderLog", mappedBy="groupUserOrder", cascade={"persist"})
     */
    private $groupUserOrderLogs;

    /**
     * GroupUserOrder constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->groupUserOrderRewards = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->groupUserOrderLogs = new ArrayCollection();

        $this->setUser($user);
        $this->setCreatedAt();
        $this->setUpdatedAt();
        $this->setCreated();
        $this->setUnPaid();
        $this->setTotal(0);
        $this->setOrderRewards(0);

    }

    public function setCreated() : self {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::CREATED;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isCreated() : bool {
        return self::CREATED == $this->getStatus();
    }

    public function setPending() : self {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::PENDING;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isCancelled() : bool {
        return self::CANCELLED == $this->getStatus();
    }

    public function setCancelled() : self {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::CANCELLED;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isPending() : bool {
        return self::PENDING == $this->getStatus();
    }

    public function setShipping() : self {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::SHIPPING;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isShipping() : bool {
        return self::SHIPPING == $this->getStatus();
    }

    public function setDelivered() : self {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::DELIVERED;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

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
        $oldStatus = $this->status;
        $newStatus = $this->status = self::RETURNING;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function setRmaReceived() : self {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::RMA_RECEIVED;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isRmaReceived() : bool {
        return self::RMA_RECEIVED == $this->getStatus();
    }

    public function setPaid() : self {
        $oldStatus = $this->paymentStatus;
        $newStatus = $this->paymentStatus = self::PAID;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromPaymentStatus($oldStatus);
        $log->setToPaymentStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isPaid() : bool {
        return self::PAID == $this->getPaymentStatus();
    }


    public function setUnPaid() : self {
        $oldStatus = $this->paymentStatus;
        $newStatus = $this->paymentStatus = self::UNPAID;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromPaymentStatus($oldStatus);
        $log->setToPaymentStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isUnPaid() : bool {
        return self::UNPAID == $this->getPaymentStatus();
    }

    public function setRefunding() : self {
        $oldStatus = $this->paymentStatus;
        $newStatus = $this->paymentStatus = self::REFUNDING;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromPaymentStatus($oldStatus);
        $log->setToPaymentStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        return $this;
    }

    public function isRefunding() : bool {
        return self::REFUNDING == $this->getPaymentStatus();
    }

    public function setRefunded() : self {
        $oldStatus = $this->paymentStatus;
        $newStatus = $this->paymentStatus = self::REFUNDED;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromPaymentStatus($oldStatus);
        $log->setToPaymentStatus($newStatus);
        $this->addGroupUserOrderLog($log);


        return $this;
    }

    public function isRefunded() : bool {
        return self::REFUNDED == $this->getPaymentStatus();
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
     * @param null|string $prePayId
     * @return GroupUserOrder
     */
    public function setPrePayId(?string $prePayId) : self {
        $this->prePayId = $prePayId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrePayId() {
        return $this->prePayId;
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
     * @param User $user
     * @return GroupUserOrder
     */
    public function setUser(User $user): self {
        $this->user =  $user;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser() : User {
        return $this->user;
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

    /**
     * 是否开团订单
     * @return bool
     */
    public function isMasterOrder() : bool {
        return $this->getUser()->getId() == $this->getGroupOrder()->getUser()->getId();
    }

    /**
     * @return array
     */
    public function getArray() : array {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatusText(),
            'paymentStatus' => $this->getPaymentStatusText(),
            'product' => $this->getGroupOrder()->getProduct()->getArray(),
            'rewards' => $this->getOrderRewards(),
            'isMasterOrder'=> $this->isMasterOrder()
        ];
    }
}
