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
use Doctrine\Common\Collections\Criteria;
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
     * @ORM\Column(name="start_date", type="integer", nullable=false)
     */
    private $startDate;

    /**
     * @var int
     * @ORM\Column(name="end_date", type="integer", nullable=false)
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
     * @var CourseStudent[]|ArrayCollection
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

    public function __construct() {
        $product = new Product();
        $product->setCourse($this);
        $this->setProduct($product);
        $this->courseStudents = new ArrayCollection();
    }

    /**
     * @param string $title
     * @param string $shortDescription
     * @param float $price
     * @param string $subject
     * @param Teacher $teacher
     * @param int $startDate
     * @param int $endDate
     * @param Region $region
     * @param string|null $address
     * @return Course
     */
    public static function factory($title, $shortDescription, $price, $subject, Teacher $teacher, $startDate, $endDate, Region $region = null, $address = null) {
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
     * @param string $subject
     */
    public function setSubject(string $subject): void
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
     * @return User[]|ArrayCollection
     */
    public function getStudentUsers($status = null){
        $criteria = Criteria::create();
        if ($status) {
            $criteria->where(Criteria::expr()->eq('status', $status));
        }

        $courseStudents =  $this->courseStudents->matching($criteria);
        $studentUsers = new ArrayCollection();
        foreach($courseStudents as $courseStudent) {
            $studentUser = $courseStudent->getStudentUser();
            if (!$studentUsers->contains($studentUser)) {
                $studentUsers->add($studentUser);
            }
        }
        return $studentUsers;
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
        return $this->getStudentUsers(CourseStudent::REGISTERED)->count();
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

        return [
            'id' => $this->getId(),
            'title' => $this->getProduct()->getTitle(),
            'subjectText' => Subject::$subjectTextArray[$this->getSubject()],
            'price' => $this->getProduct()->getPrice(),
            'shortDescription' => $this->getProduct()->getShortDescription(),
            'startDate' =>  date(self::DATE_FORMAT, $this->getStartDate()),
            'endDate' =>  date(self::DATE_FORMAT, $this->getEndDate()),
            'address' => $this->getAddress(),
            'region' => $this->getRegion() ? $this->getRegion()->getArray() : null,
            'teacher' => $this->getTeacher()->getArray(),
            'courseImages' => $courseImageArray,
            'courseSpecImages' => $courseSpecImagesArray,
            'reviewsNum' => $this->getProduct()->getTotalReviews(),
            'eligibleUserLevels' => Subject::$subjectUserLevelConstraintArray[$this->getSubject()],
            'shareImageFileId' => $this->getShareImageFile() ? $this->getShareImageFile()->getId() : null,
            'totalStudents' => $this->getTotalRegisteredStudentUsers(),
        ];
    }
}
