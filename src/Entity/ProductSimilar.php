<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductSimilarRepository")
 */
class ProductSimilar implements Dao
{
    use IdTrait,
        CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="productSimilars")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $similarProduct;

    /**
     * ProductSimilar constructor.
     */
    public function __construct()
    {
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

    public function getSimilarProduct(): ?Product
    {
        return $this->similarProduct;
    }

    public function setSimilarProduct(?Product $similarProduct): self
    {
        $this->similarProduct = $similarProduct;

        return $this;
    }
}
