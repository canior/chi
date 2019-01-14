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
     * @var CourseStudent[]
     * @ORM\OneToMany(targetEntity="CourseStudent", mappedBy="course", indexBy="studentUser", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $courseStudents;

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
     */
    public function __construct($title, $shortDescription, $price, $subject, Teacher $teacher, $startDate, $endDate, Region $region, $address = null) {
        $product = new Product();
        $product->setTitle($title);
        $product->setShortDescription($shortDescription);
        $product->setPrice($price);
        $this->setProduct($product);

        $this->setSubject($subject);
        $this->setTeacher($teacher);
        $this->setStartDate($startDate);
        $this->setEndDate($endDate);
        $this->setRegion($region);
        $this->setAddress($address);

        $this->courseStudents = new ArrayCollection();
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function getSubject(): string
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
     * @return Teacher
     */
    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    /**
     * @param Teacher $teacher
     */
    public function setTeacher(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return int
     */
    public function getStartDate(): int
    {
        return $this->startDate;
    }

    /**
     * @param int $startDate
     */
    public function setStartDate(int $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return int
     */
    public function getEndDate(): int
    {
        return $this->endDate;
    }

    /**
     * @param int $endDate
     */
    public function setEndDate(int $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return Region
     */
    public function getRegion(): Region
    {
        return $this->region;
    }

    /**
     * @param Region $region
     */
    public function setRegion(Region $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return null|string
     */
    public function getTitle() {
        return $this->getProduct()->getTitle();
    }

    /**
     * @return float
     */
    public function getPrice() {
        return $this->getProduct()->getPrice();
    }

    /**
     * @return null|string
     */
    public function getShortDescription() {
        return $this->getProduct()->getShortDescription();
    }

    public function setInActive() {
        $this->getProduct()->setInActive();
    }

    /**
     * @return CourseStudent[]
     */
    public function getCourseStudents()
    {
        return $this->courseStudents;
    }

    /**
     * 返回课程学生列表（无重复）
     *
     * @return User[]
     */
    public function getStudentUsers(){
        $students = [];
        foreach ($this->getCourseStudents() as $courseStudent) {
            if (!in_array($courseStudent->getStudentUser(), $students)) {
                $students[] = $courseStudent->getStudentUser();
            }
        }
        return $students;
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
     */
    private function addStudentUser(User $studentUser, $courseStatus) {
        $courseStudent = new CourseStudent($this, $studentUser, $courseStatus);
        $this->courseStudents->add($courseStudent);
    }

    /**
     * 学生注册
     * @param $studentUser
     */
    public function registerStudent($studentUser) {
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
            'region' => $this->getRegion()->getArray(),
            'teacher' => $this->getTeacher()->getArray(),
            'courseImages' => $courseImageArray,
            'courseSpecImages' => $courseSpecImagesArray,
        ];
    }
}