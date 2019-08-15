<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 5:12 PM
 */

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Service\Util\CommonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use App\Service\Util\FactoryUtil;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
 */
class Course implements Dao
{
    const UNLOCK_TYPE_ONE = 'buy_type_one';
    const UNLOCK_TYPE_TWO = 'buy_type_two';
    const UNLOCK_TYPE_THREE = 'buy_type_three';
    const UNLOCK_TYPE_FOUR = 'buy_type_four';

    public static $unlockTypeTexts = [
        self::UNLOCK_TYPE_ONE => '需要拼团,有价格',
        self::UNLOCK_TYPE_TWO => '需要拼团,无价格',
        self::UNLOCK_TYPE_THREE => '不需要拼团,有价格',
        self::UNLOCK_TYPE_FOUR => '不需要拼团,无价格',
    ];

    use IdTrait;

    const COURSE_SHOW_TYPE_APP = 'app';
    const COURSE_SHOW_TYPE_MINI = 'mini';
    const COURSE_SHOW_TYPE_ALL = 'all';

    public static $courseShowTypeTexts = [
        self::COURSE_SHOW_TYPE_ALL => '所有',
        self::COURSE_SHOW_TYPE_APP => 'APP',
        self::COURSE_SHOW_TYPE_MINI => '小程序',
    ];

    /**
     * @var Product
     * @ORM\OneToOne(targetEntity="App\Entity\Product", mappedBy="course", cascade={"persist"})
     */
    private $product;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $subject;

    /**
     * @var Teacher
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", cascade={"persist"}, inversedBy="courses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teacher;

    /**
     * @var int
     * @ORM\Column(name="start_date", type="integer", nullable=true)
     */
    private $startDate;

    /**
     * @var int
     * @ORM\Column(name="end_date", type="integer", nullable=true)
     */
    private $endDate;

    /**
     * @var Region
     * @ORM\ManyToOne(targetEntity="App\Entity\Region")
     */
    private $region;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tableCount;

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tableUserCount;

    /**
     * @var CourseStudent[]
     * @ORM\OneToMany(targetEntity="CourseStudent", mappedBy="course", indexBy="studentUser", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $courseStudents;


    /**
     * @var User|null $ownerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $ownerUser;

    /**
     * @var boolean
     * @ORM\Column(type="integer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $isOnline;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var integer
     */
    private $lookNum;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="course_category_id", referencedColumnName="id")
     * })
     */
    private $courseCategory;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="course_actual_category_id", referencedColumnName="id")
     * })
     */
    private $courseActualCategory;

    /**
     * @var string
     * @ORM\Column(name="unlock_type", type="string", length=20, nullable=true)
     */
    private $unlockType;

    /**
     * @var string
     * @ORM\Column(name="course_show_type", type="string", length=20, nullable=true)
     */
    private $courseShowType;

    /**
     * @var string
     * @ORM\Column(name="course_tag", type="string", length=100, nullable=true)
     */
    private $courseTag;

    /**
     * @var File | null
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $addressImageFile;

    /**
     * @var Course
     * @ORM\OneToOne(targetEntity="App\Entity\Course", inversedBy="refTargetCourse", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_course", referencedColumnName="id")
     */
    private $refCourse;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Course", mappedBy="refCourse", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $refTargetCourse;

    const CHECK_PASS = 'pass';
    const CHECK_REJECT = 'reject';

    public static $checkStatusTexts = [
        self::CHECK_PASS => '审核通过',
        self::CHECK_REJECT => '审核驳回',
    ];

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $checkStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $checkAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $initiator;
    
    /**
     * @var boolean
     * @ORM\Column(type="integer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $isShowNewest;

    public function __construct() {
        $product = new Product();
        $product->setCourse($this);
        $this->setProduct($product);
        $this->courseStudents = new ArrayCollection();
        $this->setOnline();
        $this->setLookNum(0);
        $this->setCourseTag('');
        $this->setIsShowNewest(0);
    }

    /**
     * @param $title
     * @param $shortDescription
     * @param Teacher $teacher
     * @param null $price
     * @param null $subject
     * @param null $startDate
     * @param null $endDate
     * @param Region|null $region
     * @param null $address
     * @return Course
     */
    public static function factory($title, $shortDescription, Teacher $teacher, $price = null, $subject = null, $startDate = null, $endDate = null, Region $region = null, $address = null) {
        $product = new Product();
        $product->setTitle($title);
        $product->setShortDescription($shortDescription);
        $product->setPrice($price);

        $course = new Course();
        $course->setProduct($product);
        $course->setSubject($subject);
        $course->setTeacher($teacher);
        $course->setStartDate($startDate);
        $course->setEndDate($endDate);
        $course->setRegion($region);
        $course->setAddress($address);

        $product->setCourse($course);
        return $course;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     */
    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param null|string $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return Teacher|null
     */
    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    /**
     * @param Teacher|null $teacher
     */
    public function setTeacher(?Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return User
     */
    public function getInitiator()
    {
        return $this->initiator;
    }

    /**
     * @param initiator $initiator
     * @return GroupOrder
     */
    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStartDate(): ?int
    {
        return $this->startDate;
    }

    /**
     * @return string
     */
    public function getStartDateFormatted() {
        return date(DAO::DATE_FORMAT, $this->startDate);
    }

    /**
     * @return string
     */
    public function getEndDateFormatted() {
        return date(DAO::DATE_FORMAT, $this->endDate);
    }

    /**
     * @param int|null $startDate
     */
    public function setStartDate(?int $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return int|null
     */
    public function getEndDate(): ?int
    {
        return $this->endDate;
    }

    /**
     * @param int $endDate
     */
    public function setEndDate(?int $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return Region|null
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param Region|null $region
     */
    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return int|null
     */
    public function getTableCount(): ?int
    {
        return $this->tableCount;
    }

    /**
     * @param int|null $tableCount
     */
    public function setTableCount(?int $tableCount): void
    {
        $this->tableCount = $tableCount;
    }

    /**
     * @return int|null
     */
    public function getTableUserCount(): ?int
    {
        return $this->tableUserCount;
    }

    /**
     * @param int|null $tableUserCount
     */
    public function setTableUserCount(?int $tableUserCount): void
    {
        $this->tableUserCount = $tableUserCount;
    }

    /**
     * @param string|null $title
     */
    public function setTitle($title) {
        $this->getProduct()->setTitle($title);
    }

    /**
     * @return null|string
     */
    public function getTitle() {
        return $this->getProduct()->getTitle();
    }

    /**
     * @param $price
     */
    public function setPrice($price) {
        $this->getProduct()->setPrice($price);
    }

    /**
     * @return float
     */
    public function getPrice() {
        return $this->getProduct()->getPrice();
    }

    /**
     * @param $shortDescription
     */
    public function setShortDescription($shortDescription) {
        $this->getProduct()->setShortDescription($shortDescription);
    }

    /**
     * @return null|string
     */
    public function getShortDescription() {
        return $this->getProduct()->getShortDescription();
    }

    /**
     * @param $status
     */
    public function setStatus($status) {
        $this->getProduct()->setStatus($status);
    }

    public function setInActive() {
        $this->getProduct()->setInActive();
    }

    /**
     * @param $productImages
     */
    public function setCourseImages($productImages) {
        $this->getProduct()->setProductImages($productImages);
    }

    /**
     * @return ProductImage[]|ArrayCollection
     */
    public function getCourseImages() {
        return $this->getProduct()->getProductImages();
    }

    /**
     * @return ProductSpecImage[]|Collection
     */
    public function getCourseSpecImages() {
        return $this->getProduct()->getProductSpecImages();
    }

    /**
     * @return ProductVideo[]|ArrayCollection
     */
    public function getCourseVideos() {
        return $this->getProduct()->getProductVideos();
    }

    /**
     * @return CourseStudent[]|ArrayCollection
     */
    public function getCourseStudents()
    {
        return $this->courseStudents;
    }

    /**
     * 返回课程学生列表（无重复）
     *
     * @param string $status
     * @return User[]|Collection
     */
    public function getStudentUsers($status = null){
        $students = new ArrayCollection();
        foreach ($this->getCourseStudents() as $courseStudent) {
            if (!$students->contains($courseStudent->getStudentUser())) {
                if ($status == null) {
                    $students->add($courseStudent->getStudentUser());
                } else if ($status == $courseStudent->getStatus()) {
                    $students->add($courseStudent->getStudentUser());
                }
            }
        }
        return $students;
    }

    public function isExpired() {
        return time() > strtotime(date(DAO::DATETIME_END, $this->getEndDate()));
    }

    /**
     * @return int
     */
    public function getTotalCourseStudents() {
        return $this->courseStudents->count();
    }

    /**
     * 全部课程学生,包含拒绝的学生 （无重复）
     * @return int
     */
    public function getTotalStudentUsers() {
        return count($this->getStudentUsers());
    }

    /**
     * 全部注册学生 （无重复）
     * @return int
     */
    public function getTotalRegisteredStudentUsers() {
        return count($this->getStudentUsers(CourseStudent::REGISTERED));
    }

    /**
     * 全部报到学生 （无重复）
     * @return int
     */
    public function getTotalWelcomeStudentUsers() {
        return $this->getStudentUsers(CourseStudent::WELCOME)->count();
    }


    /**
     * 全部签到学生 （无重复）
     * @return int
     */
    public function getTotalSignInStudentUsers() {
        return $this->getStudentUsers(CourseStudent::SIGNIN)->count();
    }

    /**
     * @param CourseStudent[] $courseStudents
     */
    public function setCourseStudents($courseStudents): void
    {
        $this->courseStudents = $courseStudents;
    }

    /**
     * 学生注册，报到，签到
     *
     * @param User $studentUser
     * @param $courseStatus
     * @param $memo
     */
    private function addStudentUser(User $studentUser, $courseStatus, $memo = null) {
        $courseStudent = CourseStudent::factory($this, $studentUser, $courseStatus);
        $courseStudent->setMemo($memo);
        $this->courseStudents->add($courseStudent);
        $studentUser->addCourseStudent($courseStudent);
    }

    /**
     * 学生注册
     * @param User $studentUser
     */
    public function registerStudent(User $studentUser) {
        $this->addStudentUser($studentUser, CourseStudent::REGISTERED);
    }

    /**
     * 学生报到
     * @param $studentUser
     */
    public function welcomeStudent($studentUser) {
        $this->addStudentUser($studentUser, CourseStudent::WELCOME);
    }

    /**
     * 学生签到
     * @param $studentUser
     */
    public function signInStudent($studentUser) {
        $this->addStudentUser($studentUser, CourseStudent::SIGNIN);
    }

    /**
     * 拒绝学生
     * @param $studentUser
     * @param $memo
     */
    public function refuseStudent($studentUser, $memo) {
        $this->addStudentUser($studentUser, CourseStudent::REFUSED, $memo);
    }

    /**
     * @param User $studentUser
     * @return bool true if student has already been welcomed
     */
    public function isWelcomed(User $studentUser) {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('status', CourseStudent::WELCOME));
        $criteria->andWhere(Criteria::expr()->eq('studentUser', $studentUser));

        $courseStudents =  $this->courseStudents->matching($criteria);
        return !$courseStudents->isEmpty();
    }


    /**
     * @return string
     */
    public function getSubjectText() : string
    {
        return isset(Subject::$subjectTextArray) && isset(Subject::$subjectTextArray[$this->subject]) ? Subject::$subjectTextArray[$this->subject] : $this->subject;
    }

    /**
     * @param User $studentUser
     * @return bool
     */
    public function hasStudent(User $studentUser) {
        foreach ($this->getCourseStudents() as $courseStudent) {
            if ($courseStudent->isRegistered() and $courseStudent->getStudentUser() == $studentUser) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return File|null
     */
    public function getMainCourseVideoFile() {
        if ($this->getProduct()->getProductVideos()->isEmpty()) {
            return null;
        }
        return $this->getProduct()->getProductVideos()[0]->getFile();
    }

    /**
     * @param int|null $groupOrderValidForHours
     */
    public function setGroupOrderValidForHours(?int $groupOrderValidForHours) {
        $this->getProduct()->setGroupOrderValidForHours($groupOrderValidForHours);
    }

    /**
     * @return int|null
     */
    public function getGroupOrderValidForHours() {
        return $this->getProduct()->getGroupOrderValidForHours();
    }

    /**
     * @param int|null $totalGroupUserOrdersRequired
     */
    public function setTotalGroupUserOrdersRequired(?int $totalGroupUserOrdersRequired) {
        $this->getProduct()->setTotalGroupUserOrdersRequired($totalGroupUserOrdersRequired);
    }

    /**
     * @return int|null
     */
    public function getTotalGroupUserOrdersRequired() {
        return $this->getProduct()->getTotalGroupUserOrdersRequired();
    }

    /**
     * @param File|null $shareImageFile
     */
    public function setShareImageFile(?File $shareImageFile) {
        $this->getProduct()->setShareImageFile($shareImageFile);
    }

    /**
     * @return File|null
     */
    public function getShareImageFile() {
        return $this->getProduct()->getShareImageFile();
    }

    /**
     * @param File|null $previewImageFile
     */
    public function setPreviewImageFile(?File $previewImageFile) {
        $this->getProduct()->setPreviewImageFile($previewImageFile);
    }

    /**
     * @return File|null
     */
    public function getPreviewImageFile() {
        return $this->getProduct()->getPreviewImageFile();
    }

    /**
     * @return User|null
     */
    public function getOwnerUser(): ?User
    {
        return $this->ownerUser;
    }

    /**
     * @param User|null $ownerUser
     */
    public function setOwnerUser(?User $ownerUser): void
    {
        $this->ownerUser = $ownerUser;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    /**
     * @param bool $isOnline
     */
    public function setIsOnline(bool $isOnline): void
    {
        $this->isOnline = $isOnline;
    }

    public function setOnline() {
        $this->isOnline = true;
    }

    public function setOffline() {
        $this->isOnline = false;
    }

    /**
     * @return bool
     */
    public function isShowNewest(): bool
    {
        return !empty($this->isShowNewest);
    }

    /**
     * @param bool $isShowNewest
     */
    public function setIsShowNewest(bool $isShowNewest): void
    {
        $this->isShowNewest = $isShowNewest;
    }

    /**
     * @return bool
     */
    public function isSystemSubject() {
        return Subject::SYSTEM_1 == $this->getSubject()
            or Subject::SYSTEM_2 == $this->getSubject();
    }

    /**
     * 需要后台确认的系统课
     * @return bool
     */
    public function isSpecialSystemSubject() {
        return Subject::SYSTEM_3 == $this->getSubject();
    }

    public function isPrivateDirectSubject()
    {
        return Subject::PRIVATE_DIRECTOR == $this->getSubject();
    }

    /**
     * @return bool
     */
    public function isThinkingSubject() {
        return Subject::THINKING == $this->getSubject();
    }

    /**
     * @return bool
     */
    public function isTradingSubject() {
        return Subject::TRADING == $this->getSubject();
    }

    /**
     * 是否为系统课类型包含系统课各种变种
     * @return bool
     * @author zxqc2018
     */
    public function isSystemType()
    {
        return in_array($this->getSubject(), [
            Subject::SYSTEM_1, Subject::SYSTEM_2,
            Subject::SYSTEM_3, Subject::TRADING
        ]);
    }

    /**
     * 是否为思维课类型[逻辑上类似]
     * @return bool
     * @author zxqc2018
     */
    public function isThinkType()
    {
        return in_array($this->getSubject(), [
            Subject::THINKING, Subject::PRIVATE_DIRECTOR,
        ]);
    }


    /**
     * 分钱科目
     * @return bool
     * @author zxqc2018
     */
    public function isShareMoneySubject()
    {
        return Subject::TRADING == $this->subject;
    }
    /**
     * @return array
     */
    public function getEligibleViewerUserLevels() {
        if (Subject::THINKING == $this->getSubject()) {
            return [BianxianUserLevel::THINKING, UserLevel::ADVANCED, UserLevel::ADVANCED2, UserLevel::ADVANCED3, UserLevel::PARTNER];
        } else {
            return [UserLevel::ADVANCED, UserLevel::ADVANCED2, UserLevel::ADVANCED3, UserLevel::PARTNER];
        }
    }

    /**
     * @return null|string
     */
    public function getAliyunVideoId(): ?string
    {
        return $this->getProduct()->getAliyunVideoId();
    }

    /**
     * @param null|string $aliyunVideoId
     */
    public function setAliyunVideoId(?string $aliyunVideoId): void
    {
        $this->getProduct()->setAliyunVideoId($aliyunVideoId);
    }

    /**
     * @param $priority
     */
    public function setPriority($priority) {
        $this->getProduct()->setPriority($priority);
    }

    /**
     * @return int
     */
    public function getPriority() {
        return $this->getProduct()->getPriority();
    }

    /**
     * @return int
     */
    public function getLookNum(): ?int
    {
        return $this->lookNum ?? 0;
    }

    /**
     * @param int $lookNum
     */
    public function setLookNum(int $lookNum): void
    {
        $this->lookNum = $lookNum;
    }

    /**
     * 增加观看次数
     * @param int $num
     */
    public function increaseLookNum(int $num = 1) {
        $this->lookNum += $num;
    }

    /**
     * @return Category
     */
    public function getCourseCategory(): ?Category
    {
        return $this->courseCategory;
    }

    /**
     * @param Category $courseCategory
     */
    public function setCourseCategory(?Category $courseCategory): void
    {
        $this->courseCategory = $courseCategory;
    }

    /**
     * @return Category
     */
    public function getCourseActualCategory(): ?Category
    {
        return $this->courseActualCategory;
    }

    /**
     * @param Category $courseActualCategory
     */
    public function setCourseActualCategory(?Category $courseActualCategory): void
    {
        $this->courseActualCategory = $courseActualCategory;
    }

    /**
     * @return array
     */
    public static function getUnlockTypeTexts(): array
    {
        return self::$unlockTypeTexts;
    }

    /**
     * @param array $unlockTypeTexts
     */
    public static function setUnlockTypeTexts(array $unlockTypeTexts): void
    {
        self::$unlockTypeTexts = $unlockTypeTexts;
    }

    /**
     * @return string
     */
    public function getUnlockType(): ?string
    {
        //老数据
        if (empty($this->unlockType)) {
            if (empty($this->getPrice())) {
                $this->setUnlockType(self::UNLOCK_TYPE_FOUR);
            } else {
                $this->setUnlockType(self::UNLOCK_TYPE_ONE);
            }
        }
        return $this->unlockType;
    }

    /**
     * @param string $unlockType
     */
    public function setUnlockType(string $unlockType): void
    {
        $this->unlockType = $unlockType;
    }

    /**
     * @return mixed|string
     * @author zxqc2018
     */
    public function getUnlockTypeText()
    {
        return self::$unlockTypeTexts[$this->getUnlockType()] ?? '';
    }

    /**
     * 判断用户是否有查看权限
     * @param User $user
     * @author zxqc2018
     * @return bool
     */
    public function isPermission(User $user)
    {
        $res = false;

        //检查用户等级权限
        if (!empty($this->getCourseActualCategory()) && $user->getMyUnlockCategory()->contains($this->getCourseActualCategory())) {
            $res = true;
        }

        if (!empty($res)) {
            return $res;
        }

        $newGroupUserOrder = $user->getNewestGroupUserOrder($this->getProduct());

        //判断是否购买
        if (!empty($newGroupUserOrder) && $newGroupUserOrder->isPaid()) {
            $res = true;
        } else if ($this->isFreeNoCall()) {
            $res = true;
        }

        return $res;
    }

    /**
     * 是否免费课程
     * @return bool
     * @author zxqc2018
     */
    public function isFreeNoCall()
    {
        return self::UNLOCK_TYPE_FOUR == $this->getUnlockType();
    }

    /**
     * @return string
     */
    public function getCourseShowType(): ?string
    {
        return $this->courseShowType;
    }

    /**
     * @param string $courseShowType
     */
    public function setCourseShowType(string $courseShowType): void
    {
        $this->courseShowType = $courseShowType;
    }

    /**
     * @return mixed|string
     * @author zxqc2018
     */
    public function getCourseShowTypeText()
    {
        return self::$courseShowTypeTexts[$this->getCourseShowType()] ?? '';
    }

    /**
     * @return string
     */
    public function getCourseTag(): ?string
    {
        return $this->courseTag ?? '';
    }

    /**
     * @param string $courseTag
     */
    public function setCourseTag(?string $courseTag): void
    {
        $this->courseTag = $courseTag;
    }

    /**
     * @return array
     * @author zxqc2018
     */
    public function getCourseTagArr()
    {
        $courseTag = str_replace(['，'], [','], $this->getCourseTag());
        return array_map('trim', array_filter(CommonUtil::myExplode($courseTag)));
    }

    /**
     * @return File|null
     */
    public function getAddressImageFile(): ?File
    {
        return $this->addressImageFile;
    }

    /**
     * @param File|null $addressImageFile
     */
    public function setAddressImageFile(?File $addressImageFile): void
    {
        $this->addressImageFile = $addressImageFile;
    }

    /**
     * 是否为单课程
     * @return bool
     * @author zxqc2018
     */
    public function isSingleCourse()
    {
        $res = false;
        if ($this->getCourseActualCategory() && $this->getCourseActualCategory()->isSingleCourse()) {
            $res = true;
        }

        return $res;
    }

    /**
     * 是否免费专区
     * @return bool
     * @author zxqc2018
     */
    public function isShowFreeZone()
    {
        $res = false;
        if ($this->getCourseActualCategory() && $this->getCourseActualCategory()->isShowFreeZone()) {
            $res = true;
        }

        return $res;
    }

    /**
     * 是否首页推荐
     * @return bool
     * @author zxqc2018
     */
    public function isShowRecommendZone()
    {
        $res = false;
        if ($this->getCourseActualCategory() && $this->getCourseActualCategory()->isShowRecommendZone()) {
            $res = true;
        }

        return $res;
    }

    /**
     * @return Course
     */
    public function getRefCourse(): ?Course
    {
        return $this->refCourse;
    }

    /**
     * @param Course $refCourse
     */
    public function setRefCourse(?Course $refCourse): void
    {
        $this->refCourse = $refCourse;
    }

    /**
     * @return string
     * @author zxqc2018
     */
    public function getRefCourseName()
    {
        return CommonUtil::getInsideValue($this, 'getRefCourse.getTitle', '');
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }
    /**
     * @return array
     */
    public function getArray() : array {
        $courseImageArray = [];
        foreach ($this->getProduct()->getProductImages() as $productImage) {
            $courseImageArray[] = $productImage->getArray();
        }

        $courseSpecImagesArray = [];
        foreach ($this->getProduct()->getProductSpecImages() as $productSpecImage) {
            $courseSpecImagesArray[] = $productSpecImage->getArray();
        }

        $courseVideosArray = [];
        foreach ($this->getProduct()->getProductVideos() as $productVideo) {
            $courseVideosArray[] = $productVideo->getArray();
        }

        return [
            'id' => $this->getId(),
            'productId' => $this->getProduct()->getId(),
            'title' => $this->getProduct()->getTitle(),
            'price' => $this->getProduct()->getPrice(),
            'shortDescription' => $this->getProduct()->getShortDescription(),
            'startDate' =>  $this->getStartDate() ? date(self::DATE_FORMAT, $this->getStartDate()) : '',
            'endDate' =>  $this->getEndDate() ? date(self::DATE_FORMAT, $this->getEndDate()) : '',
            'address' => $this->getAddress() ? $this->getAddress() : '-',
            'city' => $this->getCity(),
            'region' => $this->getRegion() ? $this->getRegion()->getArray() : null,
            'teacher' => $this->getTeacher()->getArray(),
            'courseImages' => $courseImageArray,
            'productImages' => $courseImageArray,
            'courseSpecImages' => $courseSpecImagesArray,
            'reviewsNum' => $this->getProduct()->getTotalActiveReviews(),
            'lookNum' => $this->getLookNum(),
            'tableCount' => $this->getTableCount(),
            'tableUserCount' => $this->getTableUserCount(),
            'maxUserCount' => $this->getTableCount()*$this->getTableUserCount(),
            'userCount' => $this->getUserNum(),
            'unpaidUserNum' => $this->getUnpaidUserNum(),
            'courseVideos' => $courseVideosArray,
            'shareCount' => $this->getShareCount(),
            'shareImageFileId' => $this->getShareImageFile() ? $this->getShareImageFile()->getId() : null,
            'previewImageFile' => $this->getPreviewImageFile() ? $this->getPreviewImageFile()->getId() : null,
            'totalStudents' => $this->getTotalStudentUsers(),
            'isOnline' => $this->isOnline,
            'eligibleUserLevels' => Subject::$subjectUserLevelConstraintArray[$this->getSubject()], //for bianxian
            'subject' => $this->getSubject(),
            'subjectText' => $this->getSubject() ? Subject::$subjectTextArray[$this->getSubject()] : null,
            'eligibleViewer' => $this->getEligibleViewerUserLevels(),
            'aliyunVideoId' => $this->getAliyunVideoId(),
            'requiredGroupUserOrders' => $this->getTotalGroupUserOrdersRequired(),
            'topCategoryName' => CommonUtil::getInsideValue($this, 'getCourseActualCategory.getParentCategory.getName', ''),
            'topCateIdentityId' => CommonUtil::getInsideValue($this, 'getCourseActualCategory.getParentCategory.getCateIdentityId', null),
            'courseCategoryName' => CommonUtil::getInsideValue($this, 'getCourseCategory.getName', ''),
            'unlockType' => $this->getUnlockType(),
            'unlockTypeText' => $this->getUnlockTypeText(),
            'courseTags' => $this->getCourseTagArr(),
            'addressImageFileId' => CommonUtil::obj2Id($this->getAddressImageFile()),
            'refCourseName' => $this->getRefCourseName(),
            'initiator' => $this->getInitiator()?$this->getInitiator()->getArray():null,
            'courseCreateTimeLine' => CommonUtil::getInsideValue($this, 'getProduct.getCreatedAtFormattedLineDate', ''),
            'checkStatus' =>$this->getCheckStatus(),
            'checkStatusText' =>$this->getCheckStatusText(),
            'progressText' =>$this->getProgress(),
        ];
    }

    // 进度
    public function getProgress(){

        $text = $this->getCheckStatusText();

        if( $this->getCheckStatus() == self::CHECK_PASS ){
            if( $this->getEndDate() < time() ){
                $text = '已结束';
            }

            if( $this->getStartDate() < time() ){
                $text = '未开始';
            }else{
                $text = '报名中';
            }
        }

        return $text;
    }

    // 已经参与人数
    public function getUserNum(){
        return FactoryUtil::groupUserOrderRepository()->getProductUserCount( $this->getProduct()->getId() );
    }

    // 未付款人数
    public function getUnpaidUserNum(){
        return FactoryUtil::groupUserOrderRepository()->getProductUserCount( $this->getProduct()->getId(),GroupUserOrder::UNPAID );
    }

    // 分享人数
    public function getShareCount(){
        return 0;
    }

    /**
     * 获取课程视频数组
     * @return array
     * @author zxqc2018
     */
    public function getCourseVideoArray()
    {
        $res = $this->getArray();
        $res['aliyunVideoUrl'] = '';
        $res['aliyunVideoImageUrl'] = '';

        if ( $this->isOnline() && !empty($this->getProduct())) {
            $refreshStatus = $this->getProduct()->refreshAliyunVideo();

            if ($refreshStatus) {
                $res['aliyunVideoUrl'] = $this->getProduct()->getAliyunVideoUrl();
                $res['aliyunVideoImageUrl'] = $this->getProduct()->getAliyunVideoImageUrl();
                if ($refreshStatus == 2) {

                    CommonUtil::entityPersist($this->getProduct());
                }
            }
        }

        return $res;
    }

    /**
     * @return string
     */
    public function getCheckStatus()
    {
        return $this->checkStatus;
    }

    public function getCheckStatusText()
    {
        return self::$checkStatusTexts[$this->getCheckStatus()] ?? '待审核';
    }

    public function isCheckPass()
    {
        return $this->checkStatus == self::CHECK_PASS;
    }

    /**
     * @param string $checkStatus
     */
    public function setCheckStatus(string $checkStatus)
    {
        $this->checkStatus = $checkStatus;
    }

    /**
     * @return string
     */
    public function getCheckAt()
    {
        return $this->checkAt;
    }

    /**
     * @param string $checkAt
     */
    public function setCheckAt(int $checkAt)
    {
        $this->checkAt = $checkAt;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }
}
