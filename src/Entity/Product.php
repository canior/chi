<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product implements Dao
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public static $statuses = [
        self::ACTIVE => '已上货',
        self::INACTIVE => '未上货'
    ];

    use IdTrait,
        StatusTrait,
        CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $originalPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $rewards;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $freight;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductImage", mappedBy="product", orphanRemoval=true, cascade={"persist","remove"})
     * @ORM\OrderBy({"priority" = "DESC"})
     */
    private $productImages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductSpecImage", mappedBy="product", orphanRemoval=true, cascade={"persist","remove"})
     * @ORM\OrderBy({"priority" = "DESC"})
     */
    private $productSpecImages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReview", mappedBy="product", fetch="EXTRA_LAZY")
     */
    private $productReviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSource", mappedBy="product")
     */
    private $shareSources;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductStatistics", mappedBy="product", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $productStatistics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductSimilar", mappedBy="product", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $productSimilars;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->setUpdatedAt(time());
        $this->setCreatedAt(time());
        $this->setActive();
        $this->setFreight(0);
        $this->productImages = new ArrayCollection();
        $this->productSpecImages = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->shareSources = new ArrayCollection();
        $this->productStatistics = new ProductStatistics();
        $this->productSimilars = new ArrayCollection();
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice($originalPrice): self
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    /**
     * 此产品的总共返现金额
     * @return float
     */
    public function getRewards()
    {
        return $this->rewards;
    }

    /**
     * 每张订单的返现
     * @return float|int
     */
    public function getOrderRewards() {
        return $this->rewards / 3;
    }

    /**
     * 每个上线传销返现
     * @return float|int
     */
    public function getUserRewards() {
        return $this->rewards / 3;
    }

    public function setRewards($rewards): self
    {
        $this->rewards = $rewards;

        return $this;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(?string $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getFreight()
    {
        return $this->freight;
    }

    public function setFreight($freight): self
    {
        $this->freight = $freight;

        return $this;
    }

    public function setActive(): self
    {
        $this->status = self::ACTIVE;
        return $this;
    }

    public function isActive() : bool {
        return self::ACTIVE == $this->getStatus();
    }

    public function setInActive(): self
    {
        $this->status = self::INACTIVE;
        return $this;
    }

    public function isInActive() : bool {
        return self::INACTIVE == $this->getStatus();
    }

    /**
     * @return Collection|ProductImage[]
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    /**
     * 返回产品主图
     * @return ProductImage|null
     */
    public function getMainProductImage() {
        foreach($this->productImages as $productImage) {
            return $productImage;
        }
        return null;
    }

    public function addProductImage(ProductImage $productImage): self
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages[] = $productImage;
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): self
    {
        if ($this->productImages->contains($productImage)) {
            $this->productImages->removeElement($productImage);
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductSpecImage[]
     */
    public function getProductSpecImages(): Collection
    {
        return $this->productSpecImages;
    }

    public function addProductSpecImage(ProductSpecImage $productSpecImage): self
    {
        if (!$this->productSpecImages->contains($productSpecImage)) {
            $this->productSpecImages[] = $productSpecImage;
            $productSpecImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductSpecImage(ProductSpecImage $productSpecImage): self
    {
        if ($this->productSpecImages->contains($productSpecImage)) {
            $this->productSpecImages->removeElement($productSpecImage);
            // set the owning side to null (unless already changed)
            if ($productSpecImage->getProduct() === $this) {
                $productSpecImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductReview[]
     */
    public function getProductReviews(): Collection
    {
        return $this->productReviews;
    }

    public function addProductReview(ProductReview $productReview): self
    {
        if (!$this->productReviews->contains($productReview)) {
            $this->productReviews[] = $productReview;
            $productReview->setProduct($this);
        }

        return $this;
    }

    public function removeProductReview(ProductReview $productReview): self
    {
        if ($this->productReviews->contains($productReview)) {
            $this->productReviews->removeElement($productReview);
            // set the owning side to null (unless already changed)
            if ($productReview->getProduct() === $this) {
                $productReview->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getArray() : array {
        $productImageArray = [];
        foreach ($this->getProductImages() as $productImage) {
            $productImageArray[] = $productImage->getArray();
        }

        $productSpecImagesArray = [];
        foreach ($this->getProductSpecImages() as $productSpecImage) {
            $productSpecImagesArray[] = $productSpecImage->getArray();
        }

        $similarProductsArray = [];
        foreach ($this->getProductSimilars() as $similarProduct) {
            $similarProductArray[] = $similarProduct->getProduct()->getArray();
        }

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'price' => $this->getPrice(),
            'originalPrice' => $this->getOriginalPrice(),
            'freight' => $this->getFreight(),
            'shortDescription' => $this->getShortDescription(),
            'rewards' => $this->getRewards(),
            'productImages' => $productImageArray,
            'productSpecImages' => $productSpecImagesArray,
            'stock' => $this->getStock(),
            'similarProducts' => $similarProductsArray
        ];
    }

    /**
     * 减少库存
     * @param int $num
     */
    public function decreaseStock(int $num = 1) {
        $this->stock -= $num;
    }

    /**
     * 增加库存
     * @param int $num
     */
    public function increaseStock(int $num = 1) {
        $this->stock += $num;
    }

    /**
     * @return Collection|ShareSource[]
     */
    public function getShareSources(): Collection
    {
        return $this->shareSources;
    }

    public function addShareSource(ShareSource $shareSource): self
    {
        if (!$this->shareSources->contains($shareSource)) {
            $this->shareSources[] = $shareSource;
            $shareSource->setProduct($this);
        }

        return $this;
    }

    public function removeShareSource(ShareSource $shareSource): self
    {
        if ($this->shareSources->contains($shareSource)) {
            $this->shareSources->removeElement($shareSource);
            // set the owning side to null (unless already changed)
            if ($shareSource->getProduct() === $this) {
                $shareSource->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductStatistics[]
     */
    public function getProductStatistics(): Collection
    {
        return $this->productStatistics;
    }

    public function addProductStatistic(ProductStatistics $productStatistic): self
    {
        if (!$this->productStatistics->contains($productStatistic)) {
            $this->productStatistics[] = $productStatistic;
            $productStatistic->setProduct($this);
        }

        return $this;
    }

    public function removeProductStatistic(ProductStatistics $productStatistic): self
    {
        if ($this->productStatistics->contains($productStatistic)) {
            $this->productStatistics->removeElement($productStatistic);
            // set the owning side to null (unless already changed)
            if ($productStatistic->getProduct() === $this) {
                $productStatistic->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductSimilar[]
     */
    public function getProductSimilars(): Collection
    {
        return $this->productSimilars;
    }

    public function addProductSimilar(ProductSimilar $productSimilar): self
    {
        if (!$this->productSimilars->contains($productSimilar)) {
            $this->productSimilars[] = $productSimilar;
            $productSimilar->setProduct($this);
        }

        return $this;
    }

    public function removeProductSimilar(ProductSimilar $productSimilar): self
    {
        if ($this->productSimilars->contains($productSimilar)) {
            $this->productSimilars->removeElement($productSimilar);
            // set the owning side to null (unless already changed)
            if ($productSimilar->getProduct() === $this) {
                $productSimilar->setProduct(null);
            }
        }

        return $this;
    }
}
