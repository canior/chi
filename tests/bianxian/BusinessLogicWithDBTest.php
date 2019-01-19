<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-18
 * Time: 5:10 PM
 */

namespace App\Tests\Bianxian;


use App\Entity\UpgradeUserOrder;
use App\Entity\UserLevel;
use App\Entity\Subject;

class BusinessLogicWithDBTest extends BianxianBaseTestCase
{
    /**
     * 1. partner1 share to child (no expires)
     * 2. partner2 share to child (no update)
     * 3. child create courseOrder1
     * 4. child create courseOrder2
     * 5. child create upgradeUserOrder
     * 6. process upgradeUserOrder and create UserAccountOrder
     * 7. create withdraw order
     */
    public function testBusinessFlow() {
        //测试合伙人1 分享用户， 合伙人2 再分享用户
        $parent1 = $this->createStudent(UserLevel::PARTNER, true);
        $parent1->setRecommandStock(100);
        $shareSource1 = $this->createShareSource($parent1, null, null, true);

        $parent2 = $this->createStudent(UserLevel::PARTNER, true);
        $parent2->setRecommandStock(100);
        $shareSource2 = $this->createShareSource($parent2, null, null, true);

        $child = $this->createStudent(UserLevel::VISITOR, true);

        $shareSource1->createShareSourceUser($child);
        $this->getEntityManager()->persist($shareSource1);
        $this->getEntityManager()->flush();

        $shareSource2->createShareSourceUser($child);
        $this->getEntityManager()->persist($shareSource2);
        $this->getEntityManager()->flush();

        $this->assertEquals($parent1, $child->getParentUser());
        $this->assertEquals(1, $parent1->getSubUsers()->count());
        $this->assertEquals(0, $parent2->getSubUsers()->count());

        //测试用户连续生成五门课订单
        $coursePrice = 100;

        $oldThinkingTeacher = $this->createTeacher(true);
        $thinkingTeacher = $this->createTeacher(true);
        $tradingTeacher = $this->createTeacher(true);
        $system1Teacher = $this->createTeacher(true);
        $system2Teacher = $this->createTeacher(true);

        $oldThinkingCourse = $this->createCourse($oldThinkingTeacher, Subject::THINKING, $coursePrice, true);
        $thinkingCourse = $this->createCourse($thinkingTeacher, Subject::THINKING, $coursePrice, true);
        $tradingCourse = $this->createCourse($tradingTeacher, Subject::TRADING, $coursePrice, true);
        $system1Course = $this->createCourse($system1Teacher, Subject::SYSTEM_1, $coursePrice, true);
        $system2Course = $this->createCourse($system2Teacher, Subject::SYSTEM_2, $coursePrice, true);


        $oldThinkingCourseOrder = $child->createCourseOrder($oldThinkingCourse);
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $oldThinkingCourseOrder->setRegistered();
        $this->getEntityManager()->persist($oldThinkingCourse);
        $this->getEntityManager()->flush();
        $this->assertEquals($child, $oldThinkingCourse->getStudentUsers()[0]);


        $thinkingCourseOrder = $child->createCourseOrder($thinkingCourse);
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $thinkingCourseOrder->setRegistered();
        $this->getEntityManager()->persist($thinkingCourseOrder);
        $this->getEntityManager()->flush();
        $this->assertEquals($child, $thinkingCourse->getStudentUsers()[0]);

        $tradingCourseOrder = $child->createCourseOrder($tradingCourse);
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $tradingCourseOrder->setRegistered();
        $this->getEntityManager()->persist($tradingCourse);
        $this->getEntityManager()->flush();
        $this->assertEquals($child, $tradingCourse->getStudentUsers()[0]);

        $system1CourseOrder = $child->createCourseOrder($system1Course);
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $system1CourseOrder->setRegistered();
        $this->getEntityManager()->persist($system1Course);
        $this->getEntityManager()->flush();
        $this->assertEquals($child, $system1Course->getStudentUsers()[0]);

        $system2CourseOrder = $child->createCourseOrder($system2Course);
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $system2CourseOrder->setRegistered();
        $this->getEntityManager()->persist($system2CourseOrder);
        $this->getEntityManager()->flush();
        $this->assertEquals($child, $system2Course->getStudentUsers()[0]);

        //测试最近一次课
        //这里必须refresh要不然不会根据doctrine倒排序
        $this->getEntityManager()->refresh($child);

        $this->assertEquals($thinkingCourse, $child->getLatestCourse(Subject::THINKING));
        $this->assertEquals($tradingCourse, $child->getLatestCourse(Subject::TRADING));
        $this->assertEquals($system1Course, $child->getLatestCourse(Subject::SYSTEM_1));
        $this->assertEquals($system2Course, $child->getLatestCourse());

        //测试用户在system2上成为合伙人
        $upgradeUserOrder = $child->createUpgradeUserOrder(UserLevel::PARTNER);
        $this->getEntityManager()->persist($upgradeUserOrder);
        $this->getEntityManager()->flush();

        //测试用户名额
        $this->assertEquals(0, $child->getUserRecommandStockOrders()->count());
        $this->assertEquals(100, $parent1->getTotalRecommandStock());
        $this->assertEquals(0, $child->getTotalRecommandStock());


        $this->assertTrue($upgradeUserOrder->isCreated());
        $this->assertTrue($child->isVisitorUser());
        $this->assertEquals(4, $upgradeUserOrder->getPotentialUserAccountOrders()->count());

        $this->assertEquals(0, $parent1->getUserAccountTotal());
        $this->assertEquals(0, $thinkingTeacher->getUser()->getUserAccountTotal());
        $this->assertEquals(0, $tradingTeacher->getUser()->getUserAccountTotal());
        $this->assertEquals(0, $system2Teacher->getUser()->getUserAccountTotal());

        $upgradeUserOrder->setApproved();
        $this->getEntityManager()->persist($upgradeUserOrder);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->refresh($upgradeUserOrder);
        $this->getEntityManager()->refresh($child);
        $this->getEntityManager()->refresh($parent1);
        $this->getEntityManager()->refresh($thinkingTeacher);
        $this->getEntityManager()->refresh($tradingTeacher);
        $this->getEntityManager()->refresh($system2Teacher);

        //检验名额订单
        $this->assertEquals(1, $parent1->getTotalRecommandStockOrders());
        $this->assertEquals(1, $child->getTotalRecommandStockOrders());
        $this->assertEquals(99, $parent1->getRecommandStock());
        $this->assertEquals(100, $child->getRecommandStock());

        $this->assertEquals(4, $upgradeUserOrder->getUserAccountOrders()->count());

        $parent1UserAccountOrder = $parent1->getUserAccountOrders()[0];
        $thinkingTeacherUserAccountOrder = $thinkingTeacher->getUser()->getUserAccountOrders()[0];
        $tradingTeacherUserAccountOrder = $tradingTeacher->getUser()->getUserAccountOrders()[0];
        $system2TeacherUserAccountOrder = $system2Teacher->getUser()->getUserAccountOrders()[0];

        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $parent1UserAccountOrder->getAmount());
        $this->assertTrue($parent1UserAccountOrder->isPaid());
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $parent1->getUserAccountTotal());
        $this->assertEquals(null, $parent1UserAccountOrder->getCourse());

        $this->assertEquals(Subject::$oldTeacherRewards[Subject::SYSTEM_2][Subject::THINKING][UserLevel::PARTNER], $thinkingTeacherUserAccountOrder->getAmount());
        $this->assertTrue($thinkingTeacherUserAccountOrder->isPaid());
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::SYSTEM_2][Subject::THINKING][UserLevel::PARTNER], $thinkingTeacher->getUser()->getUserAccountTotal());
        $this->assertEquals($thinkingCourse, $thinkingTeacherUserAccountOrder->getCourse());

        $this->assertEquals(Subject::$oldTeacherRewards[Subject::SYSTEM_2][Subject::TRADING][UserLevel::PARTNER], $tradingTeacherUserAccountOrder->getAmount());
        $this->assertTrue($tradingTeacherUserAccountOrder->isPaid());
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::SYSTEM_2][Subject::TRADING][UserLevel::PARTNER], $tradingTeacher->getUser()->getUserAccountTotal());
        $this->assertEquals($tradingCourse, $tradingTeacherUserAccountOrder->getCourse());

        $this->assertEquals(Subject::$teacherRewards[Subject::SYSTEM_2][UserLevel::PARTNER], $system2TeacherUserAccountOrder->getAmount());
        $this->assertTrue($system2TeacherUserAccountOrder->isPaid());
        $this->assertEquals(Subject::$teacherRewards[Subject::SYSTEM_2][UserLevel::PARTNER], $system2Teacher->getUser()->getUserAccountTotal());
        $this->assertEquals($system2Course, $system2TeacherUserAccountOrder->getCourse());

        //测试提现
        $withdrawOrder = $parent1->createWithdrawUserAccountOrder(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER]);
        $this->getEntityManager()->persist($withdrawOrder);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($parent1);
        $this->assertEquals(0, $parent1->getUserAccountTotal());
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $parent1->getWithDrawingTotal());

        $withdrawOrder->setPaid();
        $this->getEntityManager()->persist($withdrawOrder);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($parent1);
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $parent1->getWithDrawedTotal());
        $this->assertEquals(0, $parent1->getUserAccountTotal());


        //测试人工增加名额
        $this->assertEquals(100, $child->getRecommandStock());
        $child->createUserRecommandStockOrder(100, null, 'test manual incrase stock');
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($child);
        $this->assertEquals(200, $child->getRecommandStock());

        //测试人工减少名额
        $child->createUserRecommandStockOrder(-100, null, 'test manual descrease stock');
        $this->getEntityManager()->persist($child);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($child);
        $this->assertEquals(100, $child->getRecommandStock());


    }
}