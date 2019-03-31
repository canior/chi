<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 5:12 PM
 */

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
 */
class Course implements Dao
{
    use IdTrait;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Teacher", inversedBy="courses")
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
     * @ORM\Column(type="text")
     */
    private $address;

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


    public function __construct() {
        $product = new Product();
        $product->setCourse($this);
        $this->setProduct($product);
        $this->courseStudents = new ArrayCollection();
        $this->setOnline();
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
    public function isSystemSubject() {
        return Subject::SYSTEM_1 == $this->getSubject()
            or Subject::SYSTEM_2 == $this->getSubject();
    }

    /**
     * @return array
     */
    public function getEligibleViewerUserLevels() {
        if (Subject::THINKING == $this->getSubject()) {
            return [UserLevel::VIP, UserLevel::ADVANCED, UserLevel::ADVANCED2, UserLevel::ADVANCED3, UserLevel::PARTNER];
        } else {
            return [UserLevel::ADVANCED, UserLevel::ADVANCED2, UserLevel::ADVANCED3, UserLevel::PARTNER];
        }
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
            'startDate' =>  date(self::DATE_FORMAT, $this->getStartDate()),
            'endDate' =>  date(self::DATE_FORMAT, $this->getEndDate()),
            'address' => $this->getAddress(),
            'region' => $this->getRegion() ? $this->getRegion()->getArray() : null,
            'teacher' => $this->getTeacher()->getArray(),
            'courseImages' => $courseImageArray,
            'courseSpecImages' => $courseSpecImagesArray,
            'reviewsNum' => $this->getProduct()->getTotalActiveReviews(),
            'courseVideos' => $courseVideosArray,
            'shareImageFileId' => $this->getShareImageFile() ? $this->getShareImageFile()->getId() : null,
            'totalStudents' => $this->getTotalStudentUsers(),
            'isOnline' => $this->isOnline,
            'eligibleUserLevels' => Subject::$subjectUserLevelConstraintArray[$this->getSubject()], //for bianxian
            'subject' => $this->getSubject(),
            'subjectText' => $this->getSubject() ? Subject::$subjectTextArray[$this->getSubject()] : null,
            'eligibleViewer' => $this->getEligibleViewerUserLevels(),
        ];
    }
}
