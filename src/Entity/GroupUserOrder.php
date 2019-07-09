<?php

namespace App\Entity;

use App\Command\Notification\NotifyCompletedCouponProductCommand;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PaymentStatusTrait;
use App\Entity\Traits\StatusTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Service\Util\CommonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\TextUI\Command;
use App\Service\Util\FactoryUtil;
use App\Service\Util\MoneyUtil;

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

    const PAYMENT_CHANNEL_ALI = 'alipay';
    const PAYMENT_CHANNEL_WX = 'wechat';
    const PAYMENT_CHANNEL_WX_GZH = 'wechat_gzh';

    public static $paymentChannelTexts = [
        self::PAYMENT_CHANNEL_ALI => '支付宝',
        self::PAYMENT_CHANNEL_WX => '微信',
        self::PAYMENT_CHANNEL_WX_GZH => '微信公众号',
    ];

    public static $paymentTraceNoTypes = [
        self::PAYMENT_CHANNEL_ALI => 'ali',
        self::PAYMENT_CHANNEL_WX => 'wx'
    ];

    const CHECK_PASS = 'pass';
    const CHECK_REJECT = 'reject';

    public static $checkStatusTexts = [
        self::CHECK_PASS => '审核通过',
        self::CHECK_REJECT => '驳回',
    ];

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $checkStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $checkAt;

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
     * @var UpgradeUserOrder[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\UpgradeUserOrder", mappedBy="groupUserOrder", cascade={"persist"})
     */
    private $upgradeUserOrders;


    /**
     * @var UpgradeOrderCoupon[]|Collection
     * @ORM\OneToMany(targetEntity="App\Entity\UpgradeOrderCoupon", mappedBy="groupUserOrder", cascade={"persist"})
     */
    private $upgradeOrderCoupons;

    /**
     * @var string
     * @ORM\Column(name="out_trade_no", type="string", length=50, nullable=false)
     */
    private $outTradeNo;

    /**
     * @var string
     * @ORM\Column(name="payment_channel", type="string", length=20, nullable=false)
     */
    private $paymentChannel;
    /**
     * @var string
     * @ORM\Column(name="table_no", type="integer", nullable=true)
     */
    private $tableNo;


    /**
     * @ORM\Column(type="string", nullable=true)
     * @var int|null
     */
    private $paymentTime;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unlock_category_id", referencedColumnName="id")
     * })
     */
    private $unlockCategory;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reason;

    public function __construct() {
        $this->upgradeUserOrders = new ArrayCollection();
        $this->upgradeOrderCoupons = new ArrayCollection();
        $this->groupUserOrderRewards = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->groupUserOrderLogs = new ArrayCollection();
        $this->setOutTradeNo('');
        $this->setPaymentStatus('');
        $this->setTableNo(0);
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
                    if ($this->getProduct()->getCourse()->getSubject() == Subject::THINKING or $this->getProduct()->getCourse()->getSubject() == Subject::TRADING) {
                        if ($this->getUser()->getTeacherRecommanderUser() == null)
                            $this->getUser()->setTeacherRecommanderUser($this->getProduct()->getCourse()->getTeacher()->getUser());
                    }

                    //锁定合伙人为推荐人
                    $oldParentUser = $this->getUser()->getParentUser();
                    if ($this->getUser()->getParentUser() == null or $this->getUser()->getParentUserExpiresAt() < time()) {
                        $latestShareSource = $this->getUser()->getLatestFromShareSource();
                        if ($latestShareSource != null) {
                            $newParentUser = $this->getUser()->getLatestFromShareSource()->getUser();
                            if ($oldParentUser !== $newParentUser) {
                                $newParentUser = $newParentUser->getBianxianTopParentPartnerUser();

                                $memo = '支付成功活动订单： [' . $this->getId() . '], 变更推荐人 [' . $oldParentUser . '] => [' . $newParentUser . '], 来自分享: [' . $latestShareSource . ']';
                                $this->getUser()->setParentUser($newParentUser, $latestShareSource, $memo);
                                if ($this->getProduct()->getCourse()->isThinkType()){
                                    $this->getUser()->setParentUserExpiresAt(time() + User::PARENT_45_DAYS_EXPIRES_SECONDS);
                                } else {
                                    $this->getUser()->setParentUserExpiresAt(time() + User::PARENT_365_DAYS_EXPIRES_SECONDS);
                                }
                            }
                        }
                    }

                    //分钱逻辑
                    if (!$this->getProduct()->getCourse()->isShareMoneySubject()) { //思维课报名, 系统课1&2复训报名 系统3 特殊报名

                        $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::VIP, $this);
                        if ($jinqiuUpgradeUserOrder) {
                            $jinqiuUpgradeUserOrder->setApproved(false);
                            $this->addUpgradeUserOrder($jinqiuUpgradeUserOrder);
                        }

                        $bianxianUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::THINKING, $this);
                        if ($bianxianUpgradeUserOrder) {
                            $bianxianUpgradeUserOrder->setApproved(false);
                            $this->addUpgradeUserOrder($bianxianUpgradeUserOrder);
                        }

                        if ($this->getProduct()->isHasCoupon()) {
                            $this->createUpgradeOrderCoupons(5);
                        }

                    } else { //思维直升系统课报名
                        $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED3, $this);
                        if ($jinqiuUpgradeUserOrder) {
                            $jinqiuUpgradeUserOrder->setApproved(false);
                            $this->addUpgradeUserOrder($jinqiuUpgradeUserOrder);
                        }

                        $bianxianUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::ADVANCED, $this);
                        if ($bianxianUpgradeUserOrder) {
                            $bianxianUpgradeUserOrder->setApproved(true);
                            $this->addUpgradeUserOrder($bianxianUpgradeUserOrder);
                        }

                        //TODO 如果合伙人没有名额了怎么办
                        $topParentUser = $this->getUser()->getParentUser();
                        if ($topParentUser) {
                            $memo = '合伙人推荐系统学员成功，消除名额';
                            $topParentUser->createUserRecommandStockOrder(-1, $bianxianUpgradeUserOrder, $memo);
                        }

                        if ($this->getProduct()->isHasCoupon()) {
                            $this->createUpgradeOrderCoupons(5);
                        }
                    }
                } else { //线上课程购买
                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::VIP, $this);
                    if ($jinqiuUpgradeUserOrder) {
                        $jinqiuUpgradeUserOrder->setApproved(false);
                        $this->addUpgradeUserOrder($jinqiuUpgradeUserOrder);
                    }

                    //锁定推荐人
                    $oldParentUser = $this->getUser()->getParentUser();
                    if ($this->getUser()->getParentUser() == null or $this->getUser()->getParentUserExpiresAt() < time()) {
                        $latestShareSource = $this->getUser()->getLatestFromShareSource();
                        if ($latestShareSource != null) {
                            $newParentUser = $this->getUser()->getLatestFromShareSource()->getUser();
                            if ($oldParentUser !== $newParentUser and $newParentUser->hasRecommandRight()) {
                                $memo = '支付成功视频订单： [' . $this->getId() . '], 变更推荐人 [' . $oldParentUser . '] => [' . $newParentUser . '], 来自分享: [' . $latestShareSource . ']';
                                $this->getUser()->setParentUser($newParentUser, $latestShareSource, $memo);
                                $this->getUser()->setParentUserExpiresAt(time() + User::PARENT_45_DAYS_EXPIRES_SECONDS);
                            }
                        }
                    }

                }

                $this->setDelivered();

            } else { //产品购买订单

                //锁定推荐人
                $oldParentUser = $this->getUser()->getParentUser();
                if ($this->getUser()->getParentUser() == null or $this->getUser()->getParentUserExpiresAt() < time()) {
                    $latestShareSource = $this->getUser()->getLatestFromShareSource();
                    if ($latestShareSource != null) {
                        $newParentUser = $this->getUser()->getLatestFromShareSource()->getUser();
                        if ($oldParentUser !== $newParentUser and $newParentUser->hasRecommandRight()) {
                            $memo = '支付成功产品订单： [' . $this->getId() . '], 变更推荐人 [' . $oldParentUser . '] => [' . $newParentUser . '], 来自分享: [' . $latestShareSource . ']';
                            $this->getUser()->setParentUser($newParentUser, $latestShareSource, $memo);
                            $this->getUser()->setParentUserExpiresAt(time() + User::PARENT_365_DAYS_EXPIRES_SECONDS);
                        }
                    }
                }

                $this->setPending();

                $memo = "购买" . UserLevel::$userLevelTextArray[UserLevel::ADVANCED];
                if ($this->getProduct()->isHasCoupon()) { //荣耀vip
                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED3, $this);
                    if ($jinqiuUpgradeUserOrder) {
                        $jinqiuUpgradeUserOrder->setApproved(true);
                        $this->addUpgradeUserOrder($jinqiuUpgradeUserOrder);
                    }

                    $bianxianUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::ADVANCED, $this);
                    if ($bianxianUpgradeUserOrder) {
                        $bianxianUpgradeUserOrder->setApproved(false);
                        $this->addUpgradeUserOrder($bianxianUpgradeUserOrder);
                    }

                    $this->createUpgradeOrderCoupons(5);
                } else { //高级vip 或者 特权vip
                    $jinqiuUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED, $this);
                    if ($jinqiuUpgradeUserOrder) {
                        $jinqiuUpgradeUserOrder->setApproved(true);
                        $this->addUpgradeUserOrder($jinqiuUpgradeUserOrder);
                    }

                    $bianxianUpgradeUserOrder = $this->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::THINKING, $this);
                    if ($bianxianUpgradeUserOrder) {
                        $bianxianUpgradeUserOrder->setApproved(false);
                        $this->addUpgradeUserOrder($bianxianUpgradeUserOrder);
                    }
                }

                //每成功推荐6人即可升级为变现系统学员
                $recommander = $this->getUser()->getParentUser();
                if ($recommander != null and $recommander->getTotalUserAccountOrdersAsRecommander() >= 5) {
                    if (BianxianUserLevel::$userLevelPriorityArray[BianxianUserLevel::ADVANCED] > $recommander->getBianxianUserLevel()) {
                        $bianxianUpgradeUserOrder = $recommander->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::ADVANCED, null);
                        $bianxianUpgradeUserOrder->setApproved(false);
                        $this->addUpgradeUserOrder($bianxianUpgradeUserOrder);
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
     * @return UpgradeUserOrder[]|Collection
     */
    public function getUpgradeUserOrders()
    {
        return $this->upgradeUserOrders;
    }

    /**
     * @param UpgradeUserOrder[]|Collection $upgradeUserOrders
     */
    public function setUpgradeUserOrders($upgradeUserOrders): void
    {
        $this->upgradeUserOrders = $upgradeUserOrders;
    }

    /**
     * @param UpgradeUserOrder $upgradeUserOrder
     */
    public function addUpgradeUserOrder(UpgradeUserOrder $upgradeUserOrder) {
        if (!$this->upgradeUserOrders->contains($upgradeUserOrder))
            $this->upgradeUserOrders->add($upgradeUserOrder);
    }

    /**
     * @param $upgradeUserOrderType
     * @return UpgradeUserOrder|mixed|null
     */
    protected function getUpgradeUserOrder($upgradeUserOrderType) {
        foreach ($this->upgradeUserOrders as $upgradeUserOrder) {
            if ($upgradeUserOrder->getType() == $upgradeUserOrderType) {
                return $upgradeUserOrder;
            }
        }
        return null;
    }

    /**
     * @return UpgradeUserOrder|null
     */
    public function getJinqiuUpgradeUserOrder() {
        return $this->getUpgradeUserOrder(UpgradeUserOrder::JINQIU);
    }

    /**
     * @return UpgradeUserOrder|null
     */
    public function getBianxianUpgradeUserOrder() {
        return $this->getUpgradeUserOrder(UpgradeUserOrder::BIANXIAN);
    }

    /**
     * @return UpgradeOrderCoupon[]|Collection
     */
    public function getUpgradeOrderCoupons()
    {
        return $this->upgradeOrderCoupons;
    }

    /**
     * @param UpgradeOrderCoupon[]|Collection $upgradeOrderCoupons
     */
    public function setUpgradeOrderCoupons($upgradeOrderCoupons): void
    {
        $this->upgradeOrderCoupons = $upgradeOrderCoupons;
    }

    /**
     * @param UpgradeOrderCoupon $upgradeOrderCoupon
     */
    public function addUpgradeOrderCoupons(UpgradeOrderCoupon $upgradeOrderCoupon) {
        if (!$this->upgradeOrderCoupons->contains($upgradeOrderCoupon)) {
            $this->upgradeOrderCoupons->add($upgradeOrderCoupon);
        }
    }

    public function createUpgradeOrderCoupons($num) {
        /* 生成5个升级码 */
        for($i = 0; $i < $num; $i++) {
            $coupon = "COUP0$i";
            $upgradeOrderCoupon = UpgradeOrderCoupon::factory($this, $coupon);
            $this->addUpgradeOrderCoupons($upgradeOrderCoupon);
        }
    }

    /**
     * @return string
     */
    public function getOutTradeNo(): ?string
    {
        return $this->outTradeNo;
    }

    /**
     * @param string $outTradeNo
     */
    public function setOutTradeNo(string $outTradeNo): void
    {
        $this->outTradeNo = $outTradeNo;
    }

    /**
     * @return string
     */
    public function getPaymentChannel(): ?string
    {
        return $this->paymentChannel;
    }

    /**
     * @param string $paymentChannel
     */
    public function setPaymentChannel(string $paymentChannel): void
    {
        $this->paymentChannel = $paymentChannel;
    }

    /**
     * 是否等待支付中[app环境]
     * @return bool
     * @author zxqc2018
     */
    public function isWaitingPayWithApp()
    {
        return !empty($this->getOutTradeNo());
    }

    /**
     * @return string
     */
    public function getTableNo(): ?string
    {
        return $this->tableNo;
    }

    /**
     *  根据身份取得桌号
     * @return string
     * @author zxqc2018
     */
    public function getTableNoCheckIdentity()
    {
        $tableNo = '0';
        if ($this->getUser()->isCompletedPersonalInfo() && $this->getUser()->isSystemSubjectPrivilege(false)) {
            $tableNo = $this->tableNo;
        }
        return $tableNo;
    }

    /**
     * @param string $tableNo
     */
    public function setTableNo(string $tableNo): void
    {
        $this->tableNo = $tableNo;
    }

    /**
     * @return string
     */
    public function getCheckStatus()
    {
        return $this->checkStatus;
    }

    public function isCheckPass()
    {
        return $this->checkStatus == self::CHECK_PASS;
    }

    /**
     * @param string $checkStatus
     */
    public function setCheckStatus(string $checkStatus)
    {
        $this->checkStatus = $checkStatus;
    }

    /**
     * @return string
     */
    public function getCheckAt()
    {
        return $this->checkAt;
    }

    /**
     * @param string $checkAt
     */
    public function setCheckAt(int $checkAt)
    {
        $this->checkAt = $checkAt;
    }

    /**
     * @return mixed|string
     * @author zxqc2018
     */
    public function getPaymentChannelText()
    {
        return self::$paymentChannelTexts[$this->getPaymentChannel()] ?? '';
    }

    /**
     * @return mixed|string
     * @author zxqc2018
     */
    public function getOrderStatusText()
    {
        return self::$statuses[$this->getStatus()] ?? '';
    }

    /**
     * @return string
     */
    public function getPaymentTime()
    {
        return $this->paymentTime;
    }

    /**
     * @return string
     */
    public function getPaymentTimeText()
    {
        return $this->paymentTime?date('Y-m-d H:i:s',$this->paymentTime):null;
    }

    /**
     * @param string $paymentTime
     */
    public function setPaymentTime(int $paymentTime)
    {
        $this->paymentTime = $paymentTime;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return bool
     * @author zxqc2018
     */
    public function isUnlockCategoryOrder()
    {
        return  !empty($this->getUnlockCategory());
    }

    /**
     * @return array
     */
    public function getComplexArray() : array {

        $productReviewsArray = [];
        foreach ($this->getProductReviews() as $productReview) {
            $productReviewsArray[] = $productReview->getArray();
        }
        $appStatus = $this->getAppStatus();

        $isLevelUpTradingCourse = false;
        if ($this->getProduct()->isCourseProduct() && !$this->getProduct()->getCourse()->isOnline() &&
            $this->getProduct()->getCourse()->isTradingSubject() && $this->getProduct()->getPrice() == MoneyUtil::tradeSpecialPrice()
        ) {
            $isLevelUpTradingCourse = true;
        }

        return [
            'id' => $this->getId(),
            'groupOrderId' => $this->isGroupOrder() ? $this->getGroupOrder()->getId() : null,
            'groupOrderStatus' => $this->isGroupOrder() ? $this->getGroupOrder()->getStatus() : null,
            'status' => $this->getStatus(),
            'statusText' => $this->isCourseOrder() ? $this->getCourseStatusText() : $this->getStatusText(),
            'orderStatusText' => $this->getOrderStatusText(),
            'paymentStatus' => $this->getPaymentStatus(),
            'paymentStatusText' => $this->getPaymentStatusText(),
            'paymentChannelText' => $this->getPaymentChannelText(),
            'paymentTime' => $this->getPaymentTimeText(),
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
            'showTable' =>$this->getShowTable()['showTable'],
            'tableNo' =>$this->getTableNo(),
            'showUpdate' =>$this->getShowTable()['showUpdate'],
            'timeLine' =>$this->getTimeLine(),
            'checkStatus' =>$this->getCheckStatus(),
            'appStatus'=>$appStatus['appStatus'],
            'appStatusText'=>$appStatus['appStatusText'],
            'isLevelUpTradingCourse' => $isLevelUpTradingCourse,
        ];
    }

    /**
     * @return array
     */
    public function getArray() : array {
        $res = $this->getComplexArray();
        return $res;
    }

    /**
     * @author zxqc2018
     */
    public function getGzhArray()
    {
        $res = $this->getComplexArray();
        return $res;
    }

    public function __toString()
    {
        return '订单ID: ' . $this->getId()
            . ' 金额：￥' . $this->getTotal()
            . ' 状态：' . $this->getStatusText()
            . ' 支付状态 ' . $this->getPaymentStatusText();
    }

    /**
     * @return Category
     */
    public function getUnlockCategory(): ?Category
    {
        return $this->unlockCategory;
    }

    /**
     * @param Category $unlockCategory
     */
    public function setUnlockCategory(Category $unlockCategory): void
    {
        $this->unlockCategory = $unlockCategory;
    }

    /**
     * 生成跟踪订单号
     * @return string
     * @author zxqc2018
     */
    public function makeTraceNo()
    {
        return 'jq' . date('YmdHis') . $this->getId() . mt_rand(1000, 9999);
    }

    /**
     * 是否显示桌号
     *
     * 1.只有线下活动显示桌号
     * 2.只有思维课，系统课显示桌号
     *     1.系统课 只有高级身份显示桌号  低级身份显示升级，还有实名认证升级提升
     *     2.思维课大于1元显示桌号
     */
    public function getShowTable(){

        $showTable = false;
        $showUpdate = false;

        // 科目
        $product = $this->getProduct();
        $course = $product->getCourse();

        // 非活动订单  不显示桌号
        if( $product->isCourseProduct() && !$course->isOnline()){
            if( $course->isThinkingSubject() || $course->isPrivateDirectSubject()){
                // 思维课 todo
                if( $course->getPrice() > MoneyUtil::thinkingGeneratePrice() ){
                    $showTable = true;
                }
            }else if($course->isSystemType()){
                // 系统课
                switch ($this->getUser()->getBianxianUserLevel()) {
                    case BianxianUserLevel::VISITOR:
                    case BianxianUserLevel::THINKING:
                        $showUpdate = true;
                        break;
                    case BianxianUserLevel::ADVANCED:
                    case BianxianUserLevel::PARTNER:
                    case BianxianUserLevel::DISTRIBUTOR:
                        $showTable = true;
                        break;
                    default:
                        break;
                }

                // 未实名认证 不显示桌号
                if( !$this->getUser()->isCompletedPersonalInfo() ){
                    $showTable = false;
                }

                //查找直通车 不显示桌号
                $tradingProductId = null;
                $tradingCourse = FactoryUtil::courseRepository()->findSpecTradingCourse(MoneyUtil::tradeSpecialPrice());
                if (!empty($tradingCourse)) {
                    $tradingProductId = $tradingCourse->getProduct()->getId();
                }
                
                $isTradingProductId = false;
                if( $product->getId() == $tradingProductId ){
                    $showTable = false;
                } 
            }
        }

        return ['showTable'=>$showTable,'showUpdate'=>$showUpdate];
    }

    // APP订单状态显示
    public function getAppStatus(){
        $appStatus = 0;// 0.缺省 1.通过 2.不通过 3.待审核
        $product = $this->getProduct();

        if ($product->isCourseProduct()) {
            $course = $product->getCourse();
            if ($course->isOnline()) {
                // onlineCourse
                $appStatus = $this->getPaymentStatus()==self::PAID?1:2;
            } else {
                // offlineCourse
                if( $course->isThinkingSubject() || $course->isPrivateDirectSubject()){
                    // 思维课
                    $appStatus = $this->getPaymentStatus()==self::PAID?1:2;
                }else if($course->isSystemType()){
                    // 系统课
                    switch ($this->getUser()->getBianxianUserLevel()) {
                        case BianxianUserLevel::VISITOR:
                        case BianxianUserLevel::THINKING:
                            // if( $this->getCheckStatus() == self::CHECK_PASS ){
                                $appStatus = $this->getPaymentStatus()==self::PAID?1:2;
                            // }else if( $this->getCheckStatus() == self::CHECK_REJECT ) {
                                // $appStatus = 2;
                            // }else{
                                // $appStatus = $this->getPaymentStatus()==self::PAID?3:2;
                            // }
                            break;
                        case BianxianUserLevel::ADVANCED:
                        case BianxianUserLevel::PARTNER:
                        case BianxianUserLevel::DISTRIBUTOR:
                            $appStatus = $this->getPaymentStatus()==self::PAID?1:2;
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        //查找直通车课程id
        $tradingProductId = null;
        $tradingCourse = FactoryUtil::courseRepository()->findSpecTradingCourse(MoneyUtil::tradeSpecialPrice());
        if (!empty($tradingCourse)) {
            $tradingProductId = $tradingCourse->getProduct()->getId();
        }
        
        $isTradingProductId = false;
        if( $product->getId() == $tradingProductId ){
            $isTradingProductId = true;
        }  

        //文字Lable
        $appStatusText = null;
        switch ($appStatus) {
            case 0:
                $appStatusText = '';
                break;
            case 1:
                $appStatusText = $isTradingProductId?'升级成功':'报名通过';
                break;
            case 2:
                $appStatusText = $isTradingProductId?'升级失败':'报名不通过';
                break;
            case 3:
                $appStatusText = '待审核';
                break;
            default:
                break;
        }

        return ['appStatus'=>$appStatus,'appStatusText'=>$appStatusText];
    }

    public function getTimeLine(){
        $log = [];
        if (!$this->getProduct()->isCourseProduct() || $this->getProduct()->getCourse()->isOnline()) {
            return $log;
        }
        switch ( $this->getUser()->getBianxianUserLevel() ){
            case BianxianUserLevel::VISITOR;
            case BianxianUserLevel::THINKING;

                // 判断活动开始
                // if( $this->getCheckStatus() ){
                if( $this->getProduct()->getCourse()->getStartDate() <= time() ){
                    $log[] = ['title'=>'活动结束','time'=>date('m-d H:i',$this->getProduct()->getCourse()->getEndDate()) ];
                    $log[] = ['title'=>'活动开始','time'=>date('m-d H:i',$this->getProduct()->getCourse()->getStartDate()) ];
                }else{
                    $log[] = ['title'=>'等待活动开始','time'=>date('m-d H:i',time())];
                }
                // }

                if(  $this->getTableNo() && $this->getShowTable()['showTable'] ){
                    $log[] = ['title'=>'生成坐席号：'.$this->getTableNo().'号','time'=>date('m-d H:i',strtotime($this->getCheckAt()))];
                }

                // 审核状态
                // if( $this->getCheckStatus() == self::CHECK_PASS ){
                //     $log[] = ['title'=>'申请通过','time'=> date('m-d H:i',$this->getCheckAt())];
                // }else if( $this->getCheckStatus() == self::CHECK_PASS ){
                //     $log[] = ['title'=>'申请未通过','time'=> date('m-d H:i',$this->getCheckAt()) ];
                // }else{
                //     $log[] = ['title'=>'等待审核 ','time'=>date('m-d H:i', time() )];
                // }

                if( $this->getTableNo() ){
                    $log[] = ['title'=>'支付完成活动报名费，并申请活动请求 ','time'=>date('m-d H:i',strtotime($this->getCreatedAt()))];
                }

                break;
            case BianxianUserLevel::ADVANCED;
            case BianxianUserLevel::PARTNER;
            case BianxianUserLevel::DISTRIBUTOR;

                // 判断活动开始
                if( $this->getProduct()->getCourse()->getStartDate() <= time() ){
                    $log[] = ['title'=>'活动结束','time'=>date('m-d H:i',$this->getProduct()->getCourse()->getEndDate()) ];
                    $log[] = ['title'=>'活动开始','time'=>date('m-d H:i',$this->getProduct()->getCourse()->getStartDate()) ];
                }else{
                    $log[] = ['title'=>'等待活动开始','time'=>date('m-d H:i',time())];
                }

                if( $this->getTableNo() ){
                    $log[] = ['title'=>'生成坐席号：'.$this->getTableNo().'号','time'=>date('m-d H:i',strtotime($this->getCreatedAt()))];
                }
                if( $this->getTableNo() ){
                    $log[] = ['title'=>'活动报名成功','time'=>date('m-d H:i',strtotime($this->getCreatedAt()))];
                }
                break;
            default:
                break;
        }
        
        return $log;
    }

    /**
     * 订单是否需要合伙人确认
     * @return bool
     * @author zxqc2018
     */
    public function isNeedPartnerConfirm()
    {
        $res = false;
        if (empty($this->checkStatus) && $this->isPaid()) {
            $product = $this->getProduct();
            $res = $product->isCourseProduct() && !$product->getCourse()->isOnline() && $product->getCourse()->isSystemSubject() && !$this->getUser()->isSystemSubjectPrivilege(false);

        }
        return $res;
    }

    public function isNeedAdminConfirm()
    {
        $res = false;
        if (empty($this->checkStatus) && $this->isPaid()) {
            $product = $this->getProduct();
            $res = $product->isCourseProduct() && !$product->getCourse()->isOnline() && $product->getCourse()->isSpecialSystemSubject();
        }

        return $res;
    }
}
