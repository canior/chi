<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserSourceRepository")
 */
class UserSource
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userSources")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserShare", inversedBy="userSources")
     */
    private $otherUserShare;

    /**
     * UserSource constructor.
     */
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

    public function getOtherUserShare(): ?UserShare
    {
        return $this->otherUserShare;
    }

    public function setOtherUserShare(?UserShare $otherUserShare): self
    {
        $this->otherUserShare = $otherUserShare;

        return $this;
    }
}
