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
        self::CREATED => '创建拼团中',
        self::PENDING => '拼团中',
        self::COMPLETED => '拼团成功',
        self::EXPIRED => '拼团过期',
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
     * @ORM\OneToMany(targetEntity="GroupUserOrder", mappedBy="groupOrder", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $groupUserOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSource", mappedBy="groupOrder", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $shareSources;

    /**
     * Group constructor.
     * @param User $user
     * @param Product $product
     */
    public function __construct(User $user, Product $product)
    {
        $this->groupUserOrders = new ArrayCollection();
        $this->setUser($user);
        $this->setProduct($product);
        $this->setUpdatedAt();
        $this->setCreatedAt();
        $this->setCreated();
        $this->shareSources = new ArrayCollection();
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
     * @return Collection|GroupUserOrder[]
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

        $groupUserOrder = new GroupUserOrder($this->getUser(), $this->getProduct());
        $groupUserOrder->setGroupOrder($this);
        $groupUserOrder->setTotal($this->getProduct()->getGroupPrice());
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

        $this->setExpiredAt(time()); //TODO 需要再产品加入拼团配置
        $this->getProduct()->decreaseStock(2);//两人团减少2减库存

        $masterUserOrder = $this->getMasterGroupUserOrder();
        $masterUserOrder->setPaid();

        $this->getUser()->getOrCreateTodayUserStatistics()->increaseGroupOrderNum(1);

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
     * @param User $joiner
     * @return GroupOrder
     */
    public function setCompleted(User $joiner) : self  {
        $this->status = self::COMPLETED;

        //如果已经有了支付过的参团订单则不做任何操作
        $slaveGroupUserOrder = $this->getSlaveGroupUserOrder($joiner);
        if ($slaveGroupUserOrder != null and $slaveGroupUserOrder->isPaid()) {
            return $this;
        }

        $slaveGroupUserOrder->setPaid();
        $slaveGroupUserOrder->setPending();

        $masterGroupUserOrder = $this->getMasterGroupUserOrder();
        $masterGroupUserOrder->setPending();
        $masterGroupUserOrder->setUpdatedAt();

        $this->getUser()->getOrCreateTodayUserStatistics()->increaseGroupOrderJoinedNum(1);

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
            $groupUserOrder->setCancelled();
            $groupUserOrder->setRefunding();
            $this->getProduct()->increaseStock();
        }
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
            if (!$groupUserOrder->isMasterOrder() and $groupUserOrder->getUser()->getId() == $user->getId()) {
                return $groupUserOrder;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getArray() : array {

        $groupUserOrdersArray = [];
        foreach ($this->getGroupUserOrders() as $groupUserOrder) {
            if ($groupUserOrder->isPaid()) {
                $groupUserOrdersArray[] = $groupUserOrder->getArray();
            }
        }

        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'statusText' => $this->getStatusText(),
            'user' => $this->getUser()->getArray(),
            'product' => $this->getProduct()->getArray(),
            'groupUserOrders' => $groupUserOrdersArray,
            'createdAt' => $this->getCreatedAt(true)
        ];
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
}
