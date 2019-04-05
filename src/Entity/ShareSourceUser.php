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

        $shareSourceUser->getUser()->addFromShareSourceUser($shareSourceUser);
        $shareSourceUser->getUser()->getOrCreateTodayUserStatistics()->increaseShareNum(1);

        //分享者每次都变
        if ($shareSource->getUser() != $child)
            $shareSourceUser->getUser()->setRecommanderName($shareSource->getUser()->getDisplayName());

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
