<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserShareRepository")
 */
class UserShare implements Dao
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userShares")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $shareSource;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $page;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserSource", mappedBy="otherUserShare")
     */
    private $userSources;

    /**
     * UserShare constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->userSources = new ArrayCollection();
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

    public function getShareSource(): ?string
    {
        return $this->shareSource;
    }

    public function setShareSource(string $shareSource): self
    {
        $this->shareSource = $shareSource;

        return $this;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Collection|UserSource[]
     */
    public function getUserSources(): Collection
    {
        return $this->userSources;
    }

    public function addUserSource(UserSource $userSource): self
    {
        if (!$this->userSources->contains($userSource)) {
            $this->userSources[] = $userSource;
            $userSource->setOtherUserShare($this);
        }

        return $this;
    }

    public function removeUserSource(UserSource $userSource): self
    {
        if ($this->userSources->contains($userSource)) {
            $this->userSources->removeElement($userSource);
            // set the owning side to null (unless already changed)
            if ($userSource->getOtherUserShare() === $this) {
                $userSource->setOtherUserShare(null);
            }
        }

        return $this;
    }
}
