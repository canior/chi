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

        $child->info('get into share source ' . $shareSource->getId());

        $shareSourceUser = new ShareSourceUser();
        $shareSourceUser->setShareSource($shareSource);
        $shareSourceUser->setUser($child);
        $shareSourceUser->getUser()->getOrCreateTodayUserStatistics()->increaseShareNum(1);

        $parentUser = $shareSource->getUser();
        $oldParentUser = $child->getParentUser();

        if ($parentUser != $oldParentUser) {
            $child->info('new parent is ' . $parentUser . ', old parent is ' . $oldParentUser);

            if ($oldParentUser) {
                $child->info('old parent is not null');
                $oldParentUser->removeSubUser($child);
                $oldParentUser->info('remove child ' . $child->getId());
            }

            $memo = "推荐人变更：" . $oldParentUser . "->" . $parentUser;

            $child->info('old parent ' . $oldParentUser . ' changed to new parent ' . $parentUser);
            $child->setParentUser($parentUser, $shareSource, $memo);

            $parentUser->info('remove child ' . $child);

            $parentUser->addSubUser($child, time() + User::PARENT_EXPIRES_SECONDS);
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
            'createdAtDateFormatted' => $this->getCreatedAtDateFormatted(),
            'shareSource' => $this->getShareSource()->getArray()
        ];
    }
}
