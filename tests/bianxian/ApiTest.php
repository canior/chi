<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 10:00 PM
 */

namespace App\Tests\Bianxian;


use App\Entity\Subject;
use App\Entity\UpgradeUserOrder;
use App\Entity\UserLevel;
use App\Entity\Course;
use App\Entity\Teacher;
use App\Entity\User;
use App\Entity\ShareSource;
use App\Entity\CourseOrder;

class ApiTest extends BianxianBaseTestCase
{
    /**
     * @var User $student
     */
    private $student;

    /**
     * @var User $recommander
     */
    private $recommander;

    /**
     * @var Teacher $thinkingTeacher
     */
    private $thinkingTeacher;

    /**
     * @var Teacher $tradingTeacher
     */
    private $tradingTeacher;

    /**
     * @var Teacher $system1Teacher
     */
    private $system1Teacher;

    /**
     * @var Course $thinkingCourse
     */
    private $thinkingCourse;

    /**
     * @var Course $tradingCourse
     */
    private $tradingCourse;

    /**
     * @var Course $system1Course
     */
    private $system1Course;


    protected function setUp() {
        parent::setUp();

        $this->thinkingTeacher = $this->createTeacher(true);
        $this->tradingTeacher = $this->createTeacher(true);
        $this->system1Teacher = $this->createTeacher(true);

        $this->thinkingCourse = $this->createCourse($this->thinkingTeacher, Subject::THINKING, 380, true);
        $this->tradingCourse = $this->createCourse($this->tradingTeacher, Subject::TRADING, 390, true);
        $this->system1Course = $this->createCourse($this->system1Teacher, Subject::SYSTEM_1, 500, true);

        $this->student = $this->createStudent(UserLevel::VISITOR, true);
        $this->recommander = $this->createStudent(UserLevel::PARTNER, true);
        $this->recommander->setRecommandStock(100);
        $this->student->setParentUser($this->recommander);
    }

    public function testCourseApi() {
        $client = static::createClient();
        $client->request('GET', '/wxapi/products/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->system1Course->getArray(), $data['data']['products'][0]);
        $this->assertEquals($this->tradingCourse->getArray(), $data['data']['products'][1]);
        $this->assertEquals($this->thinkingCourse->getArray(), $data['data']['products'][2]);

        $testRedirectUrl = 'testRedirectUrl';
        $client->request('GET', '/wxapi/products/' . $this->thinkingCourse->getProduct()->getId(), ['url' => $testRedirectUrl]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->thinkingCourse->getArray(), $data['data']['product']);
        $this->assertEquals(ShareSource::REFER, $data['data']['shareSources'][ShareSource::REFER]['type']);
        $this->assertEquals(ShareSource::QUAN, $data['data']['shareSources'][ShareSource::QUAN]['type']);
    }

    public function testCourseOrderApi() {

        $studentClient = self::createClient();
        $studentClient->request('POST', '/wxapi/groupUserOrder/create', [],[], [], json_encode([
            'thirdSession' => $this->student->getId(),
            'productId' => $this->thinkingCourse->getProduct()->getId(),
        ]));
        $this->assertEquals(200, $studentClient->getResponse()->getStatusCode());
        $data = json_decode($studentClient->getResponse()->getContent(), true);

        $courseOrderRepository = $this->getEntityManager()->getRepository(CourseOrder::class);
        $courseOrder = $courseOrderRepository->findOneBy(['user' => $this->student]);

        $this->assertEquals($courseOrder->getArray(), $data['data']['groupUserOrder']);
    }

    public function testUpgradeUserOrder() {
        $studentClient = self::createClient();
        $studentClient->request('POST', '/wxapi/user/upgradeUserOrder/create', [],[], [], json_encode([
            'thirdSession' => $this->student->getId(),
            'userLevel' => UserLevel::PARTNER
        ]));
        $this->assertEquals(200, $studentClient->getResponse()->getStatusCode());
        $data = json_decode($studentClient->getResponse()->getContent(), true);

        $upgradeUserOrderRepository = $this->getEntityManager()->getRepository(UpgradeUserOrder::class);
        /**
         * @var UpgradeUserOrder $upgradeUserOrder
         */
        $upgradeUserOrder = $upgradeUserOrderRepository->findOneBy(['user' => $this->student]);
        $this->assertEquals(0, $this->recommander->getUserAccountOrders()->count());

//        $this->getEntityManager()->refresh($upgradeUserOrder);
//        $this->getEntityManager()->refresh($upgradeUserOrder->getUser());
//        $upgradeUserOrder->setApproved();
//        $this->getEntityManager()->persist($upgradeUserOrder);
//        $this->getEntityManager()->flush();


    }

}