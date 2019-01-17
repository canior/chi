<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 11:30 AM
 */

namespace App\Tests\Bianxian;
use App\Entity\Teacher;
use App\Entity\User;
use App\Entity\Course;
use App\Tests\BaseTestCase;

class BianxianBaseTestCase extends BaseTestCase
{
    /**
     * @param $userLevel
     * @param bool $isPersist
     * @return User
     */
    public function createStudent($userLevel, $isPersist = false) {
        $student = parent::createUser($isPersist);
        $student->setUserLevel($userLevel);
        return $student;
    }

    /**
     * @param bool $isPersist
     * @return Teacher
     */
    public function createTeacher($isPersist = false) {
        $teacherUser = $this->createUser($isPersist);

        $teacher = new Teacher();

        if (!$isPersist) {
            $teacher->setId(uniqid());
        }

        $teacher->setName("Teacher" . uniqid());
        $teacher->setTitle("test teacher title");
        $teacher->setDescription("teacher description");

        $teacherUser->setTeacher($teacher);
        $teacher->setUser($teacherUser);

        if ($isPersist) {
            $this->getEntityManager()->persist($teacher);
            $this->getEntityManager()->flush();
        }

        return $teacher;
    }

    /**
     * @param Teacher $teacher
     * @param $subject
     * @param $price
     * @param bool $isPersist
     * @return Course
     */
    public function createCourse(Teacher $teacher, $subject, $price, $isPersist = false) {
        $title = $subject . ' of ' . $teacher->getName();
        $shortDescription = $title;
        $starDate = time();
        $endDate = time() + 3600*24;
        $course = Course::factory($title, $shortDescription, $price, $subject, $teacher, $starDate, $endDate);

        if ($isPersist) {
            $this->getEntityManager()->persist($course);
            $this->getEntityManager()->flush();
        } else {
            $course->setId(uniqid());
        }

        return $course;
    }
}