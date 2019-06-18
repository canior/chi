<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectMetaRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="meta_type", type="string")
 * @ORM\DiscriminatorMap({"text" = "ProjectTextMeta", "banner" = "ProjectBannerMeta", "notification" = "ProjectNotificationMeta", "share" = "ProjectShareMeta", "rewards" = "ProjectRewardsMeta", "video" = "ProjectVideoMeta"})
 */
abstract class ProjectMeta implements Dao
{
    use IdTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $metaKey;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $metaValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $memo;

    public function __construct($metaKey = null) {
        $this->metaKey = $metaKey;
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

    protected function getMetaValue(): ?string
    {
        return $this->metaValue;
    }

    protected function setMetaValue(?string $metaValue): self
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

    public abstract function isTextMeta();

    public abstract function isBannerMeta();

    public abstract function isShareMeta();

    public abstract function isNotificationMeta();

    public abstract function isRewardsMeta();

    public abstract function isVideoMeta();

    /**
     * @return array
     */
    public abstract function getArray();
}
