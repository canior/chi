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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shareSourceUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ShareSource", inversedBy="shareSourceUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shareSource;

    /**
     * ShareSourceUser constructor.
     * @param ShareSource $shareSource
     * @param User $user
     */
    public function __construct(ShareSource $shareSource, User $user)
    {
        $this->setShareSource($shareSource);
        $this->setUser($user);
        $this->setCreatedAt();
        $shareSource->getUser()->getOrCreateTodayUserStatistics()->increaseShareNum(1);
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
