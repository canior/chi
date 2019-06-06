<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message implements Dao
{
    use IdTrait, CreatedAtTrait;

    /**
     * @var string|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $isRead;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $content;

    /**
     * @var User|null $ownerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * GroupUserOrderRewards constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
    }
    
    public function getIsRead(): ?string
    {
        return $this->isRead;
    }

    public function setIsRead(string $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
