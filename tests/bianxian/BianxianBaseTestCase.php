<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 11:30 AM
 */

namespace App\Tests\Bianxian;
use App\Entity\Teacher;
use App\Entity\UpgradeUserOrder;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\UserAccountOrder;
use App\Entity\UserRecommandStockOrder;
use App\Repository\CourseRepository;
use App\Repository\UpgradeUserOrderRepository;
use App\Repository\UserAccountOrderRepository;
use App\Repository\UserRecommandStockOrderRepository;
use App\Tests\BaseTestCase;

class BianxianBaseTestCase extends BaseTestCase
{
    protected function setUp() {
        parent::setUp();

        /**
         * @var UserRecommandStockOrderRepository $userRecommandStockOrderRepository
         */
        $userRecommandStockOrderRepository = $this->getEntityManager()->getRepository(UserRecommandStockOrder::class);
        foreach ($userRecommandStockOrderRepository->findAll() as $userRecommandStockOrder) {
            $this->getEntityManager()->remove($userRecommandStockOrder);
        }
        $this->getEntityManager()->flush();

        /**
         * @var UserAccountOrderRepository $userAccountOrderRepository
         */
        $userAccountOrderRepository = $this->getEntityManager()->getRepository(UserAccountOrder::class);
        foreach ($userAccountOrderRepository->findAll() as $userAccountOrder) {
            $this->getEntityManager()->remove($userAccountOrder);
        }
        $this->getEntityManager()->flush();

        /**
         * @var UpgradeUserOrderRepository $upgradeUserOrderRepository
         */
        $upgradeUserOrderRepository = $this->getEntityManager()->getRepository(UpgradeUserOrder::class);
        foreach ($upgradeUserOrderRepository->findAll() as $upgradeUserOrder) {
            $this->getEntityManager()->remove($upgradeUserOrder);
        }
        $this->getEntityManager()->flush();

        /**
         * @var CourseRepository $userRepository
         */
        $courseRepository = $this->getEntityManager()->getRepository(Course::class);
        foreach ($courseRepository->findAll() as $course) {
            $this->getEntityManager()->remove($course);
        }
        $this->getEntityManager()->flush();

        /**
         * @var Teacher $teacher
         */
        $teacherRepository = $this->getEntityManager()->getRepository(Teacher::class);
        foreach ($teacherRepository->findAll() as $teacher) {
            $this->getEntityManager()->remove($teacher);
        }
        $this->getEntityManager()->flush();

    }
    /**
     * @param $userLevel
     * @param bool $isPersist
     * @return User
     */
    public function createStudent($userLevel, $isPersist = false) {
        $student = parent::createUser($isPersist);
        $student->setUserLevel($userLevel);
        if ($isPersist) {
            $this->getEntityManager()->persist($student);
            $this->getEntityManager()->flush();
        }
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