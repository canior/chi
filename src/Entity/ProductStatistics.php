<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductStatisticsRepository")
 */
class ProductStatistics
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Product", inversedBy="productStatistics", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $reviewsNum;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $soldTotal;

    public function getId()
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getReviewsNum(): ?int
    {
        return $this->reviewsNum;
    }

    public function setReviewsNum(int $reviewsNum): self
    {
        $this->reviewsNum = $reviewsNum;

        return $this;
    }

    public function getSoldTotal()
    {
        return $this->soldTotal;
    }

    public function setSoldTotal($soldTotal): self
    {
        $this->soldTotal = $soldTotal;

        return $this;
    }
}
