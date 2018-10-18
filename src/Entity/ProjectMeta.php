<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectMetaRepository")
 */
class ProjectMeta implements Dao
{
    public function __construct(string $metaKey, string $metaValue, ?string $memo)
    {
        $this->setMetaKey($metaKey);
        $this->setMetaValue($metaValue);
        $this->setMemo($memo);
    }

    use IdTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $metaKey;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank()
     */
    private $metaValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $memo;

    public function getMetaKey(): ?string
    {
        return $this->metaKey;
    }

    public function setMetaKey(string $metaKey): self
    {
        $this->metaKey = $metaKey;

        return $this;
    }

    public function getMetaValue(): ?string
    {
        return $this->metaValue;
    }

    public function setMetaValue(?string $metaValue): self
    {
        $this->metaValue = $metaValue;

        return $this;
    }


    public function getMemo(): ?string
    {
        return $this->memo;
    }

    public function setMemo(?string $memo): self
    {
        $this->memo = $memo;

        return $this;
    }
}
