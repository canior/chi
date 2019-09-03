<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 19:39
 */

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Service\Ali\AliCommon;
use App\Service\Ali\AliVod;
use App\Service\Util\CommonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\StatusTrait;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category implements Dao
{
    use IdTrait,StatusTrait;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public static $statuses = [
        self::ACTIVE => '已发布',
        self::INACTIVE => '未发布'
    ];

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
     * @var string|null
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

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @var integer
     * @ORM\Column(name="cate_identity_id", type="integer", nullable=true)
     */
    private $cateIdentityId;

    /**
     * @var File|null
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $previewImageFile;

    public function __construct()
    {
        $this->setShowFreeZone(0);
        $this->setShowRecommendZone(0);
        $this->setSingleCourse(0);
        $this->setIsDeleted(0);
        $this->setPriority(0);
        $this->setShortDescription('');
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
     * @return string|null
     */
    public function getAliyunVideoExpiresAt(): ?string
    {
        return $this->aliyunVideoExpiresAt;
    }

    /**
     * @param string|null $aliyunVideoExpiresAt
     */
    public function setAliyunVideoExpiresAt(?string $aliyunVideoExpiresAt): void
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
        if (!empty($this->parentCategory)) {
            return $this->getParentCategory()->getName() . '->' . $this->getName();
        } else {
            return $this->getName();
        }
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
        foreach ($this->getActiveCourses() as $course) {
            $res += $course->getLookNum();
        }
        return $res;
    }

    /**
     * 获取发布的课程
     * @return ArrayCollection
     * @author zxqc2018
     */
    public function getActiveCourses()
    {
        $courses = new ArrayCollection();
        if (!$this->getCourses()->isEmpty()) {
            foreach ($this->getCourses() as $course) {
                if ($course->getProduct()->isActive()) {
                    $courses->add($course);
                }
            }
        }
        return $courses;
    }

    /**
     * @return string
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return int
     */
    public function getCateIdentityId(): ?int
    {
        return $this->cateIdentityId;
    }

    /**
     * @param int $cateIdentityId
     */
    public function setCateIdentityId(int $cateIdentityId): void
    {
        $this->cateIdentityId = $cateIdentityId;
    }

    /**
     * @return File|null
     */
    public function getPreviewImageFile(): ?File
    {
        return $this->previewImageFile;
    }

    /**
     * @param File|null $previewImageFile
     */
    public function setPreviewImageFile(?File $previewImageFile): void
    {
        $this->previewImageFile = $previewImageFile;
    }

    public function getComplexArray(?User $lookUser = null)
    {
        $courses = [];
        $sort = [];
        $categoryTags = [];
        $categoryPermission = true;
        if (empty($lookUser)) {
            $categoryPermission = false;
        }

        foreach ($this->getActiveCourses() as $key => $course) {
            $sort[$key] = $course->getProduct()->getPriority();
            $tmpArr = $course->getArray();
            $tmpArr['priority'] = $course->getProduct()->getPriority();
            if (!empty($lookUser)) {
                $tmpArr['isPermission'] = $course->isPermission($lookUser);
                if (empty($tmpArr['isPermission'])) {
                    $categoryPermission = false;
                }
            }
            $courses[] = $tmpArr;
            $categoryTags = array_merge($categoryTags, CommonUtil::getInsideValue($course, 'getCourseTagArr'));
        }
        array_multisort($sort, SORT_DESC, $courses);

        $topCateIdentityId = CommonUtil::getInsideValue($this, 'getParentCategory.getCateIdentityId', null);
        if (empty($this->getParentCategory())) {
            $topCateIdentityId = $this->getCateIdentityId();
        }

        $firstCourseArr = $courses[0] ?? [];

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parentCategoryId' => CommonUtil::getInsideValue($this, 'getParentCategory.getId', 0),
            'iconFileId' => CommonUtil::getInsideValue($this, 'getIconFile.id', 0),
            'teacher' => CommonUtil::getInsideValue($firstCourseArr, 'teacher', []),
            'mainImageId' => CommonUtil::getInsideValue($firstCourseArr, 'courseImages.0.fileId', 0),
            'lookNum' => $this->getCategoryLookNum(),
            'courseNum' => $this->getActiveCourses()->count(),
            'isSingleCourse' => $this->isSingleCourse(),
            'courses' => $courses,
            'mainCourseCreateDate' => CommonUtil::getInsideValue($firstCourseArr, 'courseCreateTimeLine', ''),
            'topCategoryName' => CommonUtil::getInsideValue($this, 'getParentCategory.getName', ''),
            'topCateIdentityId' => $topCateIdentityId,
            'shortDescription' => $this->getShortDescription() ?? '',
            'categoryTags' => array_unique($categoryTags),
            'isPermission' => $categoryPermission,
        ];
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->getComplexArray();
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


    /**
     * 视频是否过期
     * @return bool
     * @author zxqc2018
     */
    public function isAliyunVideoExpired() {
        return time() + 600 > $this->getAliyunVideoExpiresAt();
    }

    /**
     * 刷新阿里云视频地址
     * @return int
     */
    public function refreshAliyunVideo()
    {
        if (empty($this->getAliyunVideoId())) {
            return 0;
        }

        if (!$this->isAliyunVideoExpired()) {
            return 1;
        }

        try {
            $ali = new AliCommon();
            $playInfo = $ali->getPlayInfo($this->getAliyunVideoId());

            $aliyunVideoUrl = AliVod::getVideoUrl($playInfo);
            $aliyunVideoImageUrl = AliVod::getVideoImageUrl($playInfo);
            $aliyunVideoExpiresAt = AliVod::getVideoExpiresAt($playInfo);

            $this->setAliyunVideoUrl($aliyunVideoUrl);
            $this->setAliyunVideoImageUrl($aliyunVideoImageUrl);
            $this->setAliyunVideoExpiresAt($aliyunVideoExpiresAt);

            return 2;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
