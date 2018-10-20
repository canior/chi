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


    const HOME_BANNER_1 = "home_banner_1";
    const HOME_BANNER_2 = "home_banner_2";
    const HOME_BANNER_3 = "home_banner_3";

    /**
     * ProjectMeta constructor.
     * @param string $metaKey
     * @param string $metaValue
     * @param null|string $memo
     */
    public function __construct(string $metaKey, string $metaValue, ?string $memo)
    {
        $this->setMetaKey($metaKey);
        $this->setMetaValue($metaValue);
        $this->setMemo($memo);
    }

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

    /**
     * @return array
     */
    public function getArray() : array {
        return [
            'id' => $this->getId(),
            'metaKey' => $this->getMetaKey(),
            'metaValue' => $this->getMetaValue()
        ];
    }
}
