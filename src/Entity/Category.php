<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 19:39
 */

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Service\Util\CommonUtil;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var File
     *
     * @ORM\ManyToOne(targetEntity="File", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="icon_file_id", referencedColumnName="id")
     * })
     */
    private $iconFile;

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

    /**
     * @ORM\Column(name="show_free_zone",type="boolean", nullable=false)
     * @var integer
     */
    private $showFreeZone;

    /**
     * @ORM\Column(name="show_recommend_zone",type="boolean", nullable=false)
     * @var integer
     */
    private $showRecommendZone;

    /**
     * @ORM\Column(name="single_course",type="boolean", nullable=false)
     * @var integer
     */
    private $singleCourse;

    /**
     * @var integer
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var integer
     */
    private $priority;

    /**
     * @var Course[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Course", mappedBy="courseActualCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $courses;

    public function __construct()
    {
        $this->setShowFreeZone(0);
        $this->setShowRecommendZone(0);
        $this->setSingleCourse(0);
        $this->setIsDeleted(0);
        $this->setPriority(0);
    }

    /**
     * @param $name
     * @param Category|null $parentCategory
     * @return Category
     * @author zxqc2018
     */
    public static function factory($name , ?Category $parentCategory)
    {
        $category = new self();
        $category->setName($name);
        $category->setParentCategory($parentCategory);

        return $category;
    }

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

    /**
     * @return int
     */
    public function getShowFreeZone(): int
    {
        return $this->showFreeZone;
    }

    /**
     * @param int $showFreeZone
     */
    public function setShowFreeZone(int $showFreeZone): void
    {
        $this->showFreeZone = $showFreeZone;
    }

    /**
     * 是否免费专区
     * @return bool
     * @author zxqc2018
     */
    public function isShowFreeZone()
    {
        return !empty($this->getShowFreeZone());
    }

    /**
     * @return int
     */
    public function getShowRecommendZone(): int
    {
        return $this->showRecommendZone;
    }

    /**
     * @param int $showRecommendZone
     */
    public function setShowRecommendZone(int $showRecommendZone): void
    {
        $this->showRecommendZone = $showRecommendZone;
    }

    /**
     * 是否推荐专区
     * @return bool
     * @author zxqc2018
     */
    public function isShowRecommendZone()
    {
        return !empty($this->getShowRecommendZone());
    }

    /**
     * @return int
     */
    public function getSingleCourse(): int
    {
        return $this->singleCourse;
    }

    /**
     * @param int $singleCourse
     */
    public function setSingleCourse(int $singleCourse): void
    {
        $this->singleCourse = $singleCourse;
    }

    /**
     * 是否单课程类别
     * @return bool
     * @author zxqc2018
     */
    public function isSingleCourse()
    {
        return !empty($this->getSingleCourse());
    }

    /**
     * @return int
     */
    public function getIsDeleted(): int
    {
        return $this->isDeleted;
    }

    /**
     * @param int $isDeleted
     */
    public function setIsDeleted(int $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    /**
     * @return bool
     * @author zxqc2018
     */
    public function isDeleted()
    {
        return !empty($this->isDeleted);
    }
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return File
     */
    public function getIconFile(): ?File
    {
        return $this->iconFile;
    }

    /**
     * @param File $iconFile
     */
    public function setIconFile(?File $iconFile): void
    {
        $this->iconFile = $iconFile;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Course[]|ArrayCollection
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * @param Course[]|ArrayCollection $courses
     */
    public function setCourses($courses): void
    {
        $this->courses = $courses;
    }

    /**
     * @return int
     * @author zxqc2018
     */
    public function getCategoryLookNum()
    {
        $res = 0;
        foreach ($this->getCourses() as $course) {
            $res += $course->getLookNum();
        }
        return $res;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        /**
         * @var Course $firstCourse
         */
        $firstCourse = CommonUtil::getInsideValue($this, 'courses.first');

        $courses = [];
        $sort = [];
        foreach ($this->getCourses() as $key => $course) {
            $sort[$key] = $course->getProduct()->getPriority();
            $tmpArr = $course->getArray();
            $tmpArr['priority'] = $course->getProduct()->getPriority();
            $courses[] = $tmpArr;
        }
        array_multisort($sort, SORT_DESC, $courses);
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parentCategoryId' => CommonUtil::getInsideValue($this, 'getParentCategory.getId', 0),
            'iconFileId' => CommonUtil::getInsideValue($this, 'getIconFile.id', 0),
            'teacher' => CommonUtil::getInsideValue($firstCourse, 'getTeacher.array', []),
            'mainImageId' => CommonUtil::getInsideValue($firstCourse, 'getCourseImages.first.getId', 0),
            'lookNum' => $this->getCategoryLookNum(),
            'courseNum' => $this->getCourses()->count(),
            'isSingleCourse' => $this->isSingleCourse(),
            'courses' => $courses,
            'mainCourseCreateDate' => CommonUtil::getInsideValue($firstCourse, 'getProduct.getCreatedAtFormattedLineDate', ''),
            'topCategoryName' => CommonUtil::getInsideValue($this, 'getParentCategory.getName', ''),
        ];
    }

    /**
     * 取得简单数组列表
     * @return array
     */
    public function getSimpleArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parentCategoryId' => $this->getParentCategory() == null ? 0 : $this->getParentCategory()->getId(),
            'iconFileId' => CommonUtil::obj2Id($this->getIconFile()),
        ];
    }
}
