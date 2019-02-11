<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\ExpiredAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupOrderRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="group_order_type", type="string")
 * @ORM\DiscriminatorMap({"group_gift" = "GroupGiftOrder", "group_buy" = "GroupOrder"})
 */
class GroupOrder implements Dao
{
    use IdTrait,
        StatusTrait,
        ExpiredAtTrait,
        UpdatedAtTrait,
        CreatedAtTrait;

    const CREATED = 'created';
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const EXPIRED = 'expired';

    public static $statuses = [
        self::CREATED => '已创建',
        self::PENDING => '集call中',
        self::COMPLETED => '集call成功',
        self::EXPIRED => '集call过期',
    ];


    const GROUP_GIFT = 'group_gift';
    const GROUP_BUY = 'group_buy';

    public static $groupOrderType = [
        self::GROUP_GIFT => '集call',
        self::GROUP_BUY => '拼团'
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\OneToMany(targetEntity="GroupUserOrder", mappedBy="groupOrder", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $groupUserOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSource", mappedBy="groupOrder", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $shareSources;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $totalGroupUserOrdersRequired;


    /**
     * @param $groupOrderType
     * @param User $user
     * @param Product $product
     * @return GroupGiftOrder|GroupOrder
     */
    public static function factory($groupOrderType, User $user, Product $product) {
        $groupOrder = new GroupOrder();
        if ($groupOrderType == GroupOrder::GROUP_GIFT) {
            $groupOrder = new GroupGiftOrder();
        }
        $groupOrder->setUser($user);
        $groupOrder->setProduct($product);
        $groupOrder->setTotalGroupUserOrdersRequired($product->getTotalGroupUserOrdersRequired());
        $groupOrder->setCreated();

        if ($product->getGroupOrderValidForHours()) {
            $seconds =  3600 * (int)$product->getGroupOrderValidForHours();
            $groupOrder->setExpiredAt(time() + (int)$seconds);
        }


        return $groupOrder;
    }

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->groupUserOrders = new ArrayCollection();
        $this->shareSources = new ArrayCollection();
        $this->setUpdatedAt();
        $this->setCreatedAt();
        $this->setTotalGroupUserOrdersRequired(1);
        $this->status = self::CREATED;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return GroupOrder
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     * @return GroupOrder
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection|GroupUserOrder[]|CourseOrder[]
     */
    public function getGroupUserOrders(): Collection
    {
        return $this->groupUserOrders;
    }

    /**
     * 创建拼团订单
     * 1. 创建客户订单
     * @return GroupOrder
     */
    public function setCreated() : self {
        $this->status = self::CREATED;

        // 如果已经有了开团订单不做任何操作
        if ($this->getMasterGroupUserOrder() != null)
            return $this;

        $groupUserOrder = GroupUserOrder::factory($this->getUser(), $this->getProduct(), $this);
        $groupUserOrder->setGroupOrder($this);
        $groupUserOrder->setTotal($this->getProduct()->getGroupPrice() + $this->getProduct()->getFreight());
        $groupUserOrder->setOrderRewards($this->getProduct()->getGroupOrderRewards());
        $this->addGroupUserOrder($groupUserOrder);
        $this->setUpdatedAt();
        return $this;
    }

    /**
     * @return bool
     */
    public function isCreated() : bool {
        return self::CREATED == $this->getStatus();
    }

    /**
     * 拼团开始
     * 1. 创建用户订单
     * 2. 更新拼团过期时间
     * 3. 更新产品库存
     *
     * @return GroupOrder
     */
    public function setPending() : self {
        $this->status = self::PENDING;

        // 已经开团后，不做任何操作
        $masterUserOrder = $this->getMasterGroupUserOrder();
        if ($masterUserOrder->isPaid()) {
            return $this;
        }

        $this->getProduct()->decreaseStock($this->getTotalGroupUserOrdersRequired());//两人团减少2减库存

        $masterUserOrder = $this->getMasterGroupUserOrder();
        $masterUserOrder->setPending();
        $masterUserOrder->setPaid();

        $this->getUser()->getOrCreateTodayUserStatistics()->increaseGroupOrderNum(1);

        if ($this->getId()) {
            $this->getUser()->addUserCommand(CommandMessage::createNotifyPendingGroupOrderCommand($this));
            $this->getUser()->addUserCommand(CommandMessage::createNotifyExpiringGroupOrderCommand($this));
        }

        $this->setUpdatedAt();
        return $this;
    }

    /**
     * @return bool
     */
    public function isPending() : bool {
        return self::PENDING == $this->getStatus();
    }


    /**
     * 完成拼团以后需要
     * 1. 更改拼团状态
     * 2. 创建团员订单
     * 3. 改变用户订单status, paymentStatus
     *
     * @return GroupOrder
     */
    public function setCompleted() : self  {
        $this->status = self::COMPLETED;
        $this->completeAllGroupUserOrders();

        if ($this->getId()) {
            $this->getUser()->addUserCommand(CommandMessage::createNotifyCompletedGroupOrderCommand($this));
        }

        $this->setUpdatedAt();
        return $this;
    }

    /**
     * 拼团失败
     * 1. 更改拼团状态
     * 2. 所有的用户订单需要退款
     * 3. 把库存还回去
     * @return bool
     */
    public function isCompleted() : bool {
        return self::COMPLETED == $this->getStatus();
    }

    /**
     * Get completed at
     *
     * @param bool $formatted
     * @return int
     */
    public function getCompletedAt($formatted = true)
    {
        return $this->isCompleted() ? $this->getUpdatedAt($formatted) : null;
    }

    public function setExpired() : self {
        $this->status = self::EXPIRED;
        foreach ($this->getGroupUserOrders() as $groupUserOrder) {
            //这里只更新支付过的订单
            if ($groupUserOrder->isPaid()) {
                $groupUserOrder->setCancelled();
                $groupUserOrder->setRefunding();


                $this->getUser()->addUserCommand(CommandMessage::createNotifyExpiredGroupOrderCommand($this));

            }
            $this->getProduct()->increaseStock(2);
        }

        $this->getUser()->getOrCreateTodayUserStatistics()->increaseSpentTotal(-$this->getMasterGroupUserOrder()->getTotal());
        $this->setUpdatedAt();
        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired() : bool {
        return self::EXPIRED == $this->getStatus();
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

    /**
     * 返回团长订单
     * @return GroupUserOrder|null
     */
    public function getMasterGroupUserOrder() : ?GroupUserOrder{
        foreach ($this->getGroupUserOrders() as $groupUserOrder) {
            if ($groupUserOrder->isMasterOrder()) {
                return $groupUserOrder;
            }
        }
        return null;
    }

    /**
     * 返回团员订单
     * @param User $user
     * @return GroupUserOrder|null
     */
    public function getSlaveGroupUserOrder(User $user) : ?GroupUserOrder {
        foreach ($this->getGroupUserOrders() as $groupUserOrder) {
            if (!$groupUserOrder->isMasterOrder() and $groupUserOrder->getUser() == $user) {
                return $groupUserOrder;
            }
        }
        return null;
    }

    /**
     * @return Collection|ShareSource[]
     */
    public function getShareSources(): Collection
    {
        return $this->shareSources;
    }

    public function addShareSource(ShareSource $shareSource): self
    {
        if (!$this->shareSources->contains($shareSource)) {
            $this->shareSources[] = $shareSource;
            $shareSource->setGroupOrder($this);
        }

        return $this;
    }

    public function removeShareSource(ShareSource $shareSource): self
    {
        if ($this->shareSources->contains($shareSource)) {
            $this->shareSources->removeElement($shareSource);
            // set the owning side to null (unless already changed)
            if ($shareSource->getGroupOrder() === $this) {
                $shareSource->setGroupOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalGroupUserOrdersRequired(): int
    {
        return $this->totalGroupUserOrdersRequired;
    }

    /**
     * @return int
     */
    public function getTotalGroupUserOrders(): int
    {
        return $this->groupUserOrders->count();
    }

    /**
     * @param int $totalGroupUserOrdersRequired
     */
    public function setTotalGroupUserOrdersRequired(int $totalGroupUserOrdersRequired): void
    {
        $this->totalGroupUserOrdersRequired = $totalGroupUserOrdersRequired;
    }

    public function completeAllGroupUserOrders() {
        foreach($this->getGroupUserOrders() as $groupUserOrder) {
            if ($groupUserOrder instanceof CourseOrder) {
                $groupUserOrder->setRegistered();
            } else {
                $groupUserOrder->setPending();
            }
        }
    }

    /**
     * @return int
     */
    public function getRestGroupUserOrdersRequired() {
        return $this->getTotalGroupUserOrdersRequired() - $this->getTotalGroupUserOrders();
    }

    /**
     * @return array
     */
    public function getArray() : array {

        $groupUserOrdersArray = [];
        foreach ($this->getGroupUserOrders() as $groupUserOrder) {
            $groupUserOrdersArray[] = $groupUserOrder->getArray();
        }

        return [
            'id' => $this->getId(),
            'numOfOrdersRequired' => $this->getTotalGroupUserOrdersRequired(),
            'restNumOrderRequired' => $this->getRestGroupUserOrdersRequired(),
            'status' => $this->getStatus(),
            'statusText' => $this->getStatusText(),
            'user' => $this->getUser()->getArray(),
            'product' => $this->getProduct()->getArray(),
            'groupUserOrders' => $groupUserOrdersArray,
            'createdAt' => $this->getCreatedAt(true),
            'expiredAt' => $this->getExpiredAt(true)
        ];
    }
}
