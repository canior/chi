<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupUserOrderLogRepository")
 */
class GroupUserOrderLog
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupUserOrder", inversedBy="groupUserOrderLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupUserOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupUserOrderLogs")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $fromStatus;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $toStatus;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $fromPaymentStatus;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $toPaymentStatus;

    /**
     * GroupUserOrderLog constructor.
     * @param GroupUserOrder $groupUserOrder
     */
    public function __construct(GroupUserOrder $groupUserOrder)
    {
        $this->setGroupUserOrder($groupUserOrder);
        $this->setCreatedAt();
    }

    public function getGroupUserOrder(): ?GroupUserOrder
    {
        return $this->groupUserOrder;
    }

    public function setGroupUserOrder(?GroupUserOrder $groupUserOrder): self
    {
        $this->groupUserOrder = $groupUserOrder;

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

    public function getFromStatus(): ?string
    {
        return $this->fromStatus;
    }

    public function setFromStatus($fromStatus): self
    {
        $this->fromStatus = $fromStatus;

        return $this;
    }

    public function getToStatus(): ?string
    {
        return $this->toStatus;
    }

    public function setToStatus($toStatus): self
    {
        $this->toStatus = $toStatus;

        return $this;
    }

    public function getFromPaymentStatus(): ?string
    {
        return $this->fromPaymentStatus;
    }

    public function setFromPaymentStatus($fromPaymentStatus): self
    {
        $this->fromPaymentStatus = $fromPaymentStatus;

        return $this;
    }

    public function getToPaymentStatus(): ?string
    {
        return $this->toPaymentStatus;
    }

    public function setToPaymentStatus($toPaymentStatus): self
    {
        $this->toPaymentStatus = $toPaymentStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        $username = $this->getUser()->getUsername();

        $description = '用户:' . $username . '更新';

        if ($this->getToStatus() != null) {
            $description .= '订单状态：' . $this->fromStatus . '=>' . $this->toStatus;
        }

        if ($this->getToPaymentStatus() != null) {
            $description .= '支付状态：' . $this->fromPaymentStatus . '=>' . $this->toPaymentStatus;
        }

        return $description;
    }
}
