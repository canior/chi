<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * CourseStudent
 *
 * @ORM\Table(name="course_student")
 * @ORM\Entity(repositoryClass="App\Repository\CourseStudentRepository")
 */
class CourseStudent implements Dao
{
    const REGISTERED = 'registered';
    const WELCOME = 'welcome';
    const SIGNIN = 'completed';

    public static $statusTexts = [
        self::REGISTERED => '已注册',
        self::WELCOME => '已报到',
        self::SIGNIN => '签到',
    ];


    use IdTrait;
    use CreatedAtTrait;

    /**
     * @var Course
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", cascade={"persist"}, inversedBy="courseStudents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"persist"}, inversedBy="courseStudents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $studentUser;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $status;

    /**
     * CourseStudent constructor.
     * @param Course $course
     * @param User $studentUser
     * @param $status
     */
    public function __construct(Course $course, User $studentUser, $status)
    {
        $this->setCourse($course);
        $this->setStudentUser($studentUser);
        $this->setStatus($status);
        $this->setCreatedAt();
    }

    /**
     * @return Course
     */
    public function getCourse(): Course
    {
        return $this->course;
    }

    /**
     * @param Course $course
     */
    public function setCourse(Course $course): void
    {
        $this->course = $course;
    }

    /**
     * @return User
     */
    public function getStudentUser(): User
    {
        return $this->studentUser;
    }

    /**
     * @param User $studentUser
     */
    public function setStudentUser(User $studentUser): void
    {
        $this->studentUser = $studentUser;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getArray() {
        return [
            'id' => $this->getId(),
            'statusText' => self::$statusTexts[$this->getStatus()],
            'courseId' => $this->getCourse()->getId(),
            'createdAt' => $this->getCreatedAt(true)
        ];
    }

}
