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
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
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
            'shareSource' => $this->getShareSource()->getArray()
        ];
    }
}
