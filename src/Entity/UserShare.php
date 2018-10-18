<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserShareRepository")
 */
class UserShare implements Dao
{
    const CREATED = 'created';
    const VIEWED = 'viewed';
    const BOUGHT = 'bought';

    public static $statuses = [
        self::CREATED => '已分享',
        self::VIEWED => '已查看',
        self::BOUGHT => '已购买'
    ];

    use IdTrait,
        StatusTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userShares")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $parentUser;

    /**
     * UserShare constructor.
     */
    public function __construct()
    {
        $this->setCreated();
        $this->setCreatedAt(time());
    }

    public function setCreated() : self {
        $this->status = self::CREATED;
        return $this;
    }

    public function isCreated() : bool {
        return self::CREATED == $this->getStatus();
    }

    public function setViewed() : self {
        $this->status = self::VIEWED;
        return $this;
    }

    public function isViewed() : bool {
        return self::VIEWED == $this->getStatus();
    }

    public function setBought() : self {
        $this->status = self::BOUGHT;
        return $this;
    }

    public function isBought() : bool {
        return self::BOUGHT == $this->getStatus();
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

    public function getParentUser(): ?User
    {
        return $this->parentUser;
    }

    public function setParentUser(?User $parentUser): self
    {
        $this->parentUser = $parentUser;

        return $this;
    }
}
