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
    private $groupPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $originalPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $rewards;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $groupOrderRewards;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $groupOrderUserRewards;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $regularOrderRewards;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $regularOrderUserRewards;

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
     * @ORM\OneToMany(targetEntity="App\Entity\ProductReview", mappedBy="product", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $productReviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSource", mappedBy="product", orphanRemoval=true)
     */
    private $shareSources;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductStatistics", mappedBy="product", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $productStatistics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductSimilar", mappedBy="product", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $productSimilars;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupUserOrder", mappedBy="product", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $groupUserOrders;


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
        $this->productStatistics = new ArrayCollection([new ProductStatistics($this)]);
        $this->productSimilars = new ArrayCollection();
        $this->groupUserOrders = new ArrayCollection();
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

    public function setGroupPrice($amount) {
        $this->groupPrice = $amount;
    }

    public function getGroupPrice() {
        return $this->groupPrice;
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


    public function setRewards($rewards): self
    {
        $this->rewards = $rewards;
        return $this;
    }

    /**
     * @return float
     */
    public function getGroupOrderRewards()
    {
        return $this->groupOrderRewards;
    }

    /**
     * @param float $groupOrderRewards
     * @return Product
     */
    public function setGroupOrderRewards($groupOrderRewards): self
    {
        $this->groupOrderRewards = $groupOrderRewards;
        return $this;
    }

    /**
     * @return float
     */
    public function getGroupOrderUserRewards()
    {
        return $this->groupOrderUserRewards;
    }

    /**
     * @param float $groupOrderUserRewards
     * @return Product
     */
    public function setGroupOrderUserRewards($groupOrderUserRewards): self
    {
        $this->groupOrderUserRewards = $groupOrderUserRewards;
        return $this;
    }

    /**
     * @return float
     */
    public function getRegularOrderRewards()
    {
        return $this->regularOrderRewards;
    }

    /**
     * @param float $regularOrderRewards
     * @return Product
     */
    public function setRegularOrderRewards($regularOrderRewards): self
    {
        $this->regularOrderRewards = $regularOrderRewards;
        return $this;
    }

    /**
     * @return float
     */
    public function getRegularOrderUserRewards()
    {
        return $this->regularOrderUserRewards;
    }

    /**
     * @param float $regularOrderUserRewards
     * @return Product
     */
    public function setRegularOrderUserRewards($regularOrderUserRewards): self
    {
        $this->regularOrderUserRewards = $regularOrderUserRewards;
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
        return $this->productImages->isEmpty() ? null : $this->productImages->first();
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
            $similarProductsArray[] = $similarProduct->getArray();
        }

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'price' => $this->getPrice(),
            'groupPrice' => $this->getGroupPrice(),
            'originalPrice' => $this->getOriginalPrice(),
            'freight' => $this->getFreight(),
            'shortDescription' => $this->getShortDescription(),
            'rewards' => $this->getRewards(),
            'productImages' => $productImageArray,
            'productSpecImages' => $productSpecImagesArray,
            'stock' => $this->getStock(),
            'similarProducts' => $similarProductsArray,
            'soldNum' => 1000, //TODO 需要从product statistics里拿
            'reviewsNum' => 500, //TODO 需要从product statistics里拿
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

    /**
     * @return Collection|GroupUserOrder[]
     */
    public function getGroupUserOrders(): Collection
    {
        return $this->groupUserOrders;
    }

    public function addGroupUserOrder(GroupUserOrder $groupUserOrder): self
    {
        if (!$this->groupUserOrders->contains($groupUserOrder)) {
            $this->groupUserOrders[] = $groupUserOrder;
            $groupUserOrder->setProduct($this);
        }

        return $this;
    }

    public function removeGroupUserOrder(GroupUserOrder $groupUserOrder): self
    {
        if ($this->groupUserOrders->contains($groupUserOrder)) {
            $this->groupUserOrders->removeElement($groupUserOrder);
            // set the owning side to null (unless already changed)
            if ($groupUserOrder->getProduct() === $this) {
                $groupUserOrder->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return ProductStatistics
     */
    public function getOrCreateTodayProductStatistics() {
        foreach ($this->getProductStatistics() as $productStatistic) {
            if ($productStatistic->isToday()) {
                return $productStatistic;
            }
        }
        $productStatistic =  new ProductStatistics($this);
        $this->addProductStatistic($productStatistic);
        return $productStatistic;
    }
}
