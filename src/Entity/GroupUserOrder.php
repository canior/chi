<?php

namespace App\Entity;

use App\Command\Notification\NotifyCompletedCouponProductCommand;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PaymentStatusTrait;
use App\Entity\Traits\StatusTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\TextUI\Command;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupUserOrderRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="group_user_order_type", type="string")
 * @ORM\DiscriminatorMap({"product_order" = "GroupUserOrder", "course_order" = "CourseOrder"})
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
        self::SHIPPING => '已发货',
        self::DELIVERED => '已收货',
        self::RETURNING => '退货中',
        self::RMA_RECEIVED => '收到退货'
    ];

    public static $courseStatuses = [
        self::CREATED => '待注册',
        self::CANCELLED => '已取消',
        self::DELIVERED => '已注册',
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

    const GROUP_MASTER = '团长';
    const GROUP_MEMBER = '团员';
    const REGULAR = '普通';


    const PRODUCT_ORDER = 'product_order';
    const COURSE_ORDER = 'course_order';

    public static $groupUserOrderType = [
        self::PRODUCT_ORDER => '产品订单',
        self::COURSE_ORDER => '课程订单'
    ];


    /**
     * @ORM\ManyToOne(targetEntity="GroupOrder", inversedBy="groupUserOrders")
     * @ORM\JoinColumn(nullable=true)
     */
    private $groupOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAddress")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userAddress;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupUserOrders", cascade={"persist"})
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
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrderRewards", mappedBy="groupUserOrder", cascade="persist", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $groupUserOrderRewards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReview", mappedBy="groupUserOrder", fetch="EXTRA_LAZY", orphanRemoval=true, cascade={"persist"} )
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $productReviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrderLog", mappedBy="groupUserOrder", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $groupUserOrderLogs;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="groupUserOrders")
     */
    private $product;

    /**
     * @var UpgradeUserOrder|null
     * @ORM\OneToOne(targetEntity="App\Entity\UpgradeUserOrder", mappedBy="groupUserOrder", cascade={"persist"})
     */
    private $upgradeUserOrder;


    public function __construct() {
        $this->groupUserOrderRewards = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->groupUserOrderLogs = new ArrayCollection();
        $this->setCreatedAt();
        $this->setUpdatedAt();
    }

    /**
     * GroupUserOrder constructor.
     * @param User $user
     * @param Product $product
     * @param GroupOrder|null $groupOrder
     * @return GroupUserOrder|CourseOrder
     */
    public static function factory(User $user, Product $product, ?GroupOrder $groupOrder = null)
    {
        $groupUserOrder = new GroupUserOrder();
        if ($product->getCourse()) {
            $groupUserOrder = new CourseOrder();
        }

        $groupUserOrder->setUser($user);
        $groupUserOrder->setProduct($product);
        $groupUserOrder->setCreated();
        $groupUserOrder->setUnPaid();

        $groupUserOrder->setTotal($product->getPrice() + $product->getFreight());
        $groupUserOrder->setOrderRewards($product->getGroupOrderRewards());

        if ($groupOrder) {
            $groupUserOrder->setGroupOrder($groupOrder);
            $groupOrder->addGroupUserOrder($groupUserOrder);
        }

        return $groupUserOrder;
    }

    /**
     * 是否课程订单
     * @return bool
     */
    public function isCourseOrder(){
        return false;
    }

    /**
     * @return $this
     */
    public function setCreated() {
        $oldStatus = $this->status;
        $newStatus = $this->status = self::CREATED;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $log = new GroupUserOrderLog($this);
        $log->setFromStatus($oldStatus);
        $log->setToStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        $this->getProduct()->getOrCreateTodayProductStatistics()->increaseOrderNum(1);

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

    /**
     * @return $this
     */
    public function setCancelled() {
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

        //订单确认收货后，此时给上线传销返现

        if ($this->getUser()->getParentUser() != null) {
            $parentRewards = $this->getProduct()->getRegularOrderUserRewards();
            if ($this->isGroupOrder()) {
                $parentRewards = $this->getProduct()->getGroupOrderUserRewards();
            }
            $userRewards = new GroupUserOrderRewards();
            $userRewards->setUser($this->getUser()->getParentUser());
            $userRewards->setGroupUserOrder($this);
            $userRewards->setUserRewards($parentRewards);
            $this->addGroupUserOrderReward($userRewards);

            //发放传销返现
            $this->getUser()->getParentUser()->increasePendingTotalRewards($parentRewards);
            $this->getUser()->getParentUser()->getOrCreateTodayUserStatistics()->increaseUserRewardsTotal($this->getOrderRewards());
        }

        return $this;
    }

    public function isDelivered() : bool {
        return self::DELIVERED == $this->getStatus();
    }

    public function setReturning() : self {
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

    public function isReturning() : bool {
        return $this->status == self::RETURNING;
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

        $this->getUser()->getOrCreateTodayUserStatistics()->increaseGroupUserOrderNum(-1);
        $this->getProduct()->getOrCreateTodayProductStatistics()->increaseRmaUsers(1);

        return $this;
    }

    public function isRmaReceived() : bool {
        return self::RMA_RECEIVED == $this->getStatus();
    }

    public function setPaid() {
        $oldStatus = $this->paymentStatus;
        $newStatus = $this->paymentStatus = self::PAID;

        if ($oldStatus == $newStatus) {
            return $this;
        }

        $this->getUser()->addGroupUserOrder($this);

        $log = new GroupUserOrderLog($this);
        $log->setFromPaymentStatus($oldStatus);
        $log->setToPaymentStatus($newStatus);
        $this->addGroupUserOrderLog($log);

        // 拼团订单不需要考虑分钱
        if ($this->getGroupOrder()) {
            if ($this->getGroupOrder()->getRestGroupUserOrdersRequired() == 0) { //如果是最后一张拼团订单则触发整个拼团完成
                $this->getGroupOrder()->setCompleted();
            }
        } else {
            if ($this->getProduct()->isCourseProduct()) { //在线视频购买或者线下课程购买订单

                // 线下活动注册
                if (!$this->getProduct()->getCourse()->isOnline()) {

                    //锁定讲师
                    if ($this->getProduct()->getCourse()->getSubject() == Subject::THINKING) {
                        $this->getUser()->setTeacherRecommanderUser($this->getProduct()->getCourse()->getTeacher()->getUser());
                    }

                    //锁定合伙人为推荐人
                    $oldParentUser = $this->getUser()->getParentUser();
                    if ($this->getUser()->getParentUser() == null or $this->getUser()->getParentUserExpiresAt() < time()) {
                        if ($this->getUser()->getLatestFromShareSource() != null) {
                            $newParentUser = $this->getUser()->getLatestFromShareSource()->getUser();
                            if ($oldParentUser !== $newParentUser) {
                                $newParentUser = $newParentUser->getBianxianTopParentPartnerUser();
                                $this->getUser()->setParentUser($newParentUser);

                                if (!$this->getProduct()->getCourse()->isSystemSubject()) { //思维课报名
                                    $this->getUser()->setParentUserExpiresAt(User::PARENT_45_DAYS_EXPIRES_SECONDS);
                                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::VIP, $this);
                                    $jinqiuUpgradeUserOrder->setApproved();

                                    $bianxianUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::THINKING, $this);
                                    $bianxianUpgradeUserOrder->setApproved();
                                } else { //系统课报名
                                    $this->getUser()->setParentUserExpiresAt(User::PARENT_365_DAYS_EXPIRES_SECONDS);

                                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED3, $this);
                                    $jinqiuUpgradeUserOrder->setApproved();

                                    $bianxianUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::ADVANCED, $this);
                                    $bianxianUpgradeUserOrder->setApproved();

                                    //TODO 如果合伙人没有名额了怎么办
                                    $newParentUser->createUserRecommandStockOrder(-1);

                                    if ($this->getProduct()->isHasCoupon()) {
                                        $memo = "购买" . UserLevel::$userLevelTextArray[UserLevel::ADVANCED3];

                                        //推送用户coupon
                                        $this->getUser()->addUserCommand(CommandMessage::createNotifyCompletedCouponProductCommand($this->getId()));
                                    }
                                }

                            }
                        }
                    }

                    $this->setDelivered();

                    if ($this->getUpgradeUserOrder()) {
                        $this->getUpgradeUserOrder()->setApproved();
                    }

                } else {
                    // TODO: 在线视频还未实现购买功能
                }

            } else { //产品购买订单

                //锁定推荐人
                $oldParentUser = $this->getUser()->getParentUser();
                if ($this->getUser()->getParentUser() == null or $this->getUser()->getParentUserExpiresAt() < time()) {
                    if ($this->getUser()->getLatestFromShareSource() != null) {
                        $newParentUser = $this->getUser()->getLatestFromShareSource()->getUser();
                        if ($oldParentUser !== $newParentUser and $newParentUser->hasRecommandRight()) {
                            $this->getUser()->setParentUser($newParentUser);
                            $this->getUser()->setParentUserExpiresAt(User::PARENT_365_DAYS_EXPIRES_SECONDS);
                        }
                    }
                }

                $this->setPending();

                $memo = "购买" . UserLevel::$userLevelTextArray[UserLevel::ADVANCED];
                if ($this->getProduct()->isHasCoupon()) { //荣耀vip
                    $memo = "购买" . UserLevel::$userLevelTextArray[UserLevel::ADVANCED3];
                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED3, $this);
                    $jinqiuUpgradeUserOrder->setApproved();

                    //推送用户coupon
                    $this->getUser()->addUserCommand(CommandMessage::createNotifyCompletedCouponProductCommand($this->getId()));
                } else {
                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED, $this);
                    $jinqiuUpgradeUserOrder->setApproved();
                }

                //每成功推荐6人即可升级为变现系统学员
                $recommander = $this->getUser()->getParentUser();
                if ($recommander != null and $recommander->getTotalUserAccountOrdersAsRecommander() > 5) {
                    if (BianxianUserLevel::$userLevelPriorityArray[BianxianUserLevel::ADVANCED] > $recommander->getBianxianUserLevel()) {
                        $recommander->upgradeBianxianUserLevel(BianxianUserLevel::ADVANCED);
                    }
                }
            }
        }

        return $this;
    }

    public function isPaid() : bool {
        return self::PAID == $this->getPaymentStatus();
    }


    /**
     * @return $this
     */
    public function setUnPaid() {
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

    /**
     * Get completed at
     *
     * @param bool $formatted
     * @return int
     */
    public function getCompletedAt($formatted = true)
    {
        return $this->isPaid() ? $this->getUpdatedAt($formatted) : null;
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

        //添加到退款队列
        $this->getUser()->addUserCommand(CommandMessage::createRefundOrderCommand($this));

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

        $this->getUser()->getOrCreateTodayUserStatistics()->increaseSpentTotal(-$this->getTotal());

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
        if ($this->isGroupOrder())
            return $this->getUser() == $this->getGroupOrder()->getUser();
        return false;
    }

    /**
     * @return string
     */
    public function getRole(): string {
        return $this->isMasterOrder() ? self::GROUP_MASTER : self::GROUP_MEMBER;
    }

    /**
     * @return bool
     */
    public function isGroupOrder() {
        return $this->getGroupOrder() != null;
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

    public function setPaymentStatus(string $paymentStatus)
    {
        switch ($paymentStatus) {
            case self::UNPAID: return $this->setUnPaid();
            case self::PAID: return $this->setPaid();
            case self::REFUNDING: return $this->setRefunding();
            case self::REFUNDED: return $this->setRefunded();
        }
    }

    /**
     * @return string
     */
    public function getCourseStatusText() : string
    {
        return isset(self::$courseStatuses) && isset(self::$courseStatuses[$this->status]) ? self::$courseStatuses[$this->status] : $this->status;
    }

    /**
     * @return UpgradeUserOrder|null
     */
    public function getUpgradeUserOrder(): ?UpgradeUserOrder
    {
        return $this->upgradeUserOrder;
    }

    /**
     * @param UpgradeUserOrder|null $upgradeUserOrder
     */
    public function setUpgradeUserOrder(?UpgradeUserOrder $upgradeUserOrder): void
    {
        $this->upgradeUserOrder = $upgradeUserOrder;
    }


    /**
     * @return array
     */
    public function getArray() : array {

        $productReviewsArray = [];
        foreach ($this->getProductReviews() as $productReview) {
            $productReviewsArray[] = $productReview->getArray();
        }

        return [
            'id' => $this->getId(),
            'groupOrderId' => $this->isGroupOrder() ? $this->getGroupOrder()->getId() : null,
            'groupOrderStatus' => $this->isGroupOrder() ? $this->getGroupOrder()->getStatus() : null,
            'status' => $this->getStatus(),
            'statusText' => $this->isCourseOrder() ? $this->getCourseStatusText() : $this->getStatusText(),
            'paymentStatus' => $this->getPaymentStatus(),
            'paymentStatusText' => $this->getPaymentStatusText(),
            'product' => $this->getProduct()->getArray(),
            'rewards' => $this->getOrderRewards(),
            'isMasterOrder'=> $this->isMasterOrder(),
            'wxPrePayId' => $this->getPrePayId(),
            'user' => $this->getUser()->getArray(),
            'productReviews' => $productReviewsArray,
            'createdAt' => $this->getCreatedAt(true),
            'paymentTotal' => $this->getTotal(),
            'address' => $this->getUserAddress() == null ? null : $this->getUserAddress()->getArray(),
            'carrierName' => $this->getCarrierName(),
            'trackingNo' => $this->getTrackingNo(),
        ];
    }

    public function __toString()
    {
        return '订单ID: ' . $this->getId()
            . ' 金额：￥' . $this->getTotal()
            . ' 状态：' . $this->getStatusText()
            . ' 支付状态 ' . $this->getPaymentStatusText();
    }
}
