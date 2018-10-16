<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductReviewRepository")
 */
class ProductReview implements Dao
{
    use IdTrait,
        CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="productReviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupOrder", inversedBy="productReviews")
     */
    private $groupOrder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rate;

    /**
     * @ORM\Column(type="text")
     */
    private $review;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReviewImage", mappedBy="productReview")
     */
    private $productReviewImages;

    /**
     * ProductReview constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->productReviewImages = new ArrayCollection();
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

    public function getGroupOrder(): ?GroupOrder
    {
        return $this->groupOrder;
    }

    public function setGroupOrder(?GroupOrder $groupOrder): self
    {
        $this->groupOrder = $groupOrder;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(?string $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): self
    {
        $this->review = $review;

        return $this;
    }

    /**
     * @return Collection|ProductReviewImage[]
     */
    public function getProductReviewImages(): Collection
    {
        return $this->productReviewImages;
    }

    public function addProductReviewImage(ProductReviewImage $productReviewImage): self
    {
        if (!$this->productReviewImages->contains($productReviewImage)) {
            $this->productReviewImages[] = $productReviewImage;
            $productReviewImage->setProductReview($this);
        }

        return $this;
    }

    public function removeProductReviewImage(ProductReviewImage $productReviewImage): self
    {
        if ($this->productReviewImages->contains($productReviewImage)) {
            $this->productReviewImages->removeElement($productReviewImage);
            // set the owning side to null (unless already changed)
            if ($productReviewImage->getProductReview() === $this) {
                $productReviewImage->setProductReview(null);
            }
        }

        return $this;
    }
}
