<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 19:39
 */

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category implements Dao
{
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_category_id", referencedColumnName="id")
     * })
     */
    private $parentCategory;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string | null
     */
    private $aliyunVideoId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $aliyunVideoUrl;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $aliyunVideoImageUrl;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var int|null
     */
    private $aliyunVideoExpiresAt;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Category
     */
    public function getParentCategory(): ?Category
    {
        return $this->parentCategory;
    }

    /**
     * @param Category $parentCategory
     */
    public function setParentCategory(?Category $parentCategory): void
    {
        $this->parentCategory = $parentCategory;
    }

    /**
     * @return string|null
     */
    public function getAliyunVideoId(): ?string
    {
        return $this->aliyunVideoId;
    }

    /**
     * @param string|null $aliyunVideoId
     */
    public function setAliyunVideoId(?string $aliyunVideoId): void
    {
        $this->aliyunVideoId = $aliyunVideoId;
    }

    /**
     * @return string|null
     */
    public function getAliyunVideoUrl(): ?string
    {
        return $this->aliyunVideoUrl;
    }

    /**
     * @param string|null $aliyunVideoUrl
     */
    public function setAliyunVideoUrl(?string $aliyunVideoUrl): void
    {
        $this->aliyunVideoUrl = $aliyunVideoUrl;
    }

    /**
     * @return string|null
     */
    public function getAliyunVideoImageUrl(): ?string
    {
        return $this->aliyunVideoImageUrl;
    }

    /**
     * @param string|null $aliyunVideoImageUrl
     */
    public function setAliyunVideoImageUrl(?string $aliyunVideoImageUrl): void
    {
        $this->aliyunVideoImageUrl = $aliyunVideoImageUrl;
    }

    /**
     * @return int|null
     */
    public function getAliyunVideoExpiresAt(): ?int
    {
        return $this->aliyunVideoExpiresAt;
    }

    /**
     * @param int|null $aliyunVideoExpiresAt
     */
    public function setAliyunVideoExpiresAt(?int $aliyunVideoExpiresAt): void
    {
        $this->aliyunVideoExpiresAt = $aliyunVideoExpiresAt;
    }

    public function __toString()
    {
        return $this->getName();
    }
    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parentCategoryId' => $this->getParentCategory() == null ? 0 : $this->getParentCategory()->getId(),
        ];
    }
}
