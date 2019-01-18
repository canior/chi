<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

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
     * @param User $child
     * @return ShareSourceUser
     */
    public static function factory(ShareSource $shareSource, User $child)
    {
        $shareSourceUser = new ShareSourceUser();
        $shareSourceUser->setShareSource($shareSource);
        $shareSourceUser->setUser($child);
        $shareSourceUser->getUser()->getOrCreateTodayUserStatistics()->increaseShareNum(1);

        $parentUser = $shareSource->getUser();

        if ($child->getParentUser() == null) {
            if ($parentUser->isPartnerUser()) { //如果受邀者的推荐人为空，并且推荐人是合伙人
                $parentUser->addSubUser($child, time() + User::PARENT_EXPIRES_SECONDS);
            }
        } else {
            if ($child->getParentUserExpiresAt() < time()) { //如果受邀者的推荐人不为空，但之前推荐人的时间过期
                $oldParentUser = $child->getParentUser();
                $oldParentUser->removeSubUser($child);
                $parentUser->addSubUser($child, time() + User::PARENT_EXPIRES_SECONDS);
            }
        }

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
