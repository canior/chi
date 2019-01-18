<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShareSourceUserRepository")
 */
class ShareSourceUser implements Dao
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shareSourceUsers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ShareSource", inversedBy="shareSourceUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shareSource;

    /**
     * 处理用户分享逻辑
     *
     *
     *
     * @param ShareSource $shareSource
     * @param User $user
     * @return ShareSourceUser
     */
    public static function factory(ShareSource $shareSource, User $user)
    {
        $shareSourceUser = new ShareSourceUser();
        $shareSourceUser->setShareSource($shareSource);
        $shareSourceUser->setUser($user);
        $shareSourceUser->getUser()->getOrCreateTodayUserStatistics()->increaseShareNum(1);
        $user->setParentUser($shareSource->getUser());

        return $shareSourceUser;
    }

    public function __construct()
    {
        $this->setCreatedAt();
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

    public function getShareSource(): ?ShareSource
    {
        return $this->shareSource;
    }

    public function setShareSource(?ShareSource $shareSource): self
    {
        $this->shareSource = $shareSource;

        return $this;
    }

    /**
     * @return array
     */
    public function getArray() : array {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->getArray(),
            'createdAt' => $this->getCreatedAt(true),
            'shareSourceId' => $this->getShareSource()->getArray()
        ];
    }
}
