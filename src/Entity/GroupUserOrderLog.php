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
     * @ORM\JoinColumn(nullable=false)
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
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
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

    public function setFromStatus(?string $fromStatus): self
    {
        $this->fromStatus = $fromStatus;

        return $this;
    }

    public function getToStatus(): ?string
    {
        return $this->toStatus;
    }

    public function setToStatus(?string $toStatus): self
    {
        $this->toStatus = $toStatus;

        return $this;
    }

    public function getFromPaymentStatus(): ?string
    {
        return $this->fromPaymentStatus;
    }

    public function setFromPaymentStatus(?string $fromPaymentStatus): self
    {
        $this->fromPaymentStatus = $fromPaymentStatus;

        return $this;
    }

    public function getToPaymentStatus(): ?string
    {
        return $this->toPaymentStatus;
    }

    public function setToPaymentStatus(?string $toPaymentStatus): self
    {
        $this->toPaymentStatus = $toPaymentStatus;

        return $this;
    }
}
