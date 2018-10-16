<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductReviewImageRepository")
 */
class ProductReviewImage implements Dao
{
    use IdTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProductReview", inversedBy="productReviewImages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productReview;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File")
     * @ORM\JoinColumn(nullable=false)
     */
    private $imageFile;

    public function getProductReview(): ?ProductReview
    {
        return $this->productReview;
    }

    public function setProductReview(?ProductReview $productReview): self
    {
        $this->productReview = $productReview;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }
}
