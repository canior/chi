<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductVideoRepository")
 */
class ProductVideo implements Dao
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="productVideos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File")
     * @ORM\JoinColumn(nullable=false)
     */
    private $file;

    /**
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * ProductImage constructor.
     */
    public function __construct()
    {
        $this->setPriority(0);
        $this->setCreatedAt();
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getArray() : array {
        return [
            'id' => $this->getId(),
            'type' =>  $this->getFile()->getType(),
            'fileId' => $this->getFile()->getId(),
        ];
    }
}
