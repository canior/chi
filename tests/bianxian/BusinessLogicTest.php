<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 1:19 AM
 */

namespace App\Tests\Bianxian;


use App\Entity\CourseStudent;
use App\Entity\ShareSourceUser;
use App\Entity\Subject;
use App\Entity\UserAccountOrder;
use App\Entity\UserLevel;
use App\Entity\CourseOrder;
use App\Form\CourseStudentType;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class BusinessLogicTest extends BianxianBaseTestCase
{
    /**
     * 测试学员会务费订单流程
     */
    public function testCreateGroupUserOrder()
    {
        $student = parent::createStudent(UserLevel::VISITOR);
        $teacher = parent::createTeacher();
        $price = 380;
        $course = parent::createCourse($teacher, Subject::THINKING, $price);

        $order = $student->createCourseOrder($course);

        //待注册，未支付
        $this->assertTrue($order->isCreated());
        $this->assertTrue($order->isUnPaid());
        $this->assertEquals($student->getGroupUserOrders()[0], $order);
        $this->assertNull($student->getParentUser());
        $this->assertTrue($order->isCourseOrder());

        //学生支付 及课程注册
        $order->setPaid();
        $this->assertTrue($order->isPaid());
        $this->assertTrue($order->isRegistered());
        $this->assertEquals($price, $order->getTotal());
        $this->assertNull($student->getParentUser());

        $this->assertEquals(1, $course->getCourseStudents()->count());
        $courseStudents = $course->getStudentUsers(CourseStudent::REGISTERED);
        $this->assertEquals(1, $courseStudents->count());
        $this->assertEquals($student, $courseStudents[0]);
    }

    /**
     * 测试学员报到、签到流程
     */
    public function testCreateCourseStudent() {
        $student1 = parent::createStudent(UserLevel::VISITOR);
        $student2 = parent::createStudent(UserLevel::ADVANCED);
        $teacher = parent::createTeacher();
        $price = 380;
        $course = parent::createCourse($teacher, Subject::THINKING, $price);

        $course->welcomeStudent($student1);
        $course->signInStudent($student2);
        $course->signInStudent($student1);
        $course->signInStudent($student1);

        $welcomeCount = $course->getStudentUsers(CourseStudent::WELCOME)->count();
        $this->assertEquals(1, $welcomeCount);

        $signInCount = $course->getStudentUsers(CourseStudent::SIGNIN)->count();
        $this->assertEquals(2, $signInCount);

        $courseStudentsCount = $course->getCourseStudents()->count();
        $this->assertEquals(4, $courseStudentsCount);
    }


    /**
     * 测试思维课上学员升级高级会员
     */
    public function testThinkingUpgradeAdvanced() {

        $recommanderWithStock = parent::createStudent(UserLevel::PARTNER);
        $stock = 10;
        $recommanderWithStock->setRecommandStock($stock);

        $recommanderWithoutStock = parent::createStudent(UserLevel::PARTNER);

        $studentWithoutRecommander = parent::createStudent(UserLevel::VISITOR);

        $studentWithRecommanderWithStock = parent::createStudent(UserLevel::VISITOR);
        $studentWithRecommanderWithStock->setParentUser($recommanderWithStock);

        $studentWithRecommanderWithoutStock = parent::createStudent(UserLevel::VISITOR);
        $studentWithRecommanderWithoutStock->setParentUser($recommanderWithoutStock);

        $teacher = parent::createTeacher();
        $price = 380;
        $course = parent::createCourse($teacher, Subject::THINKING, $price);

        $teacher2 = parent::createTeacher();
        $course2 = parent::createCourse($teacher2, Subject::THINKING, $price);


        //1. 无推荐人学员升级, 只分钱给最近的讲师
        $course2->registerStudent($studentWithoutRecommander);
        $course->registerStudent($studentWithoutRecommander);

        //创建
        $upgradeUserOrder = $studentWithoutRecommander->createUpgradeUserOrder(UserLevel::ADVANCED);
        $this->assertEquals(UserLevel::$userLevelPriceArray[UserLevel::ADVANCED], $upgradeUserOrder->getTotal());
        $this->assertTrue($upgradeUserOrder->isCreated());

        //处理中
        $upgradeUserOrder->addPayment(3000, '第一笔钱');
        $this->assertTrue($upgradeUserOrder->isPending());

        //升级通过去分钱
        $upgradeUserOrder->setApproved();
        $this->assertTrue($upgradeUserOrder->isApproved());

        $this->assertEquals(0, $teacher->getUser()->getUserAccountOrders()->count());
        $this->assertEquals(0, $teacher->getUser()->getUserAccountTotal());

        $this->assertEquals(1, $teacher2->getUser()->getUserAccountOrders()->count());
        $teacherAccountOrder2 = $teacher2->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$teacherRewards[Subject::THINKING][UserLevel::ADVANCED], $teacherAccountOrder2->getAmount());
        $this->assertTrue(!$teacherAccountOrder2->isPaid());
        $this->assertEquals($teacherAccountOrder2->getAmount(), $teacher2->getUser()->getUserAccountTotal());


        //2. 有库存推荐人学员升级，分钱给最近讲师和有库存的推荐人
        $recommanderWithStock->setUserAccountTotal(0);
        $teacher->getUser()->setUserAccountTotal(0);
        $teacher2->getUser()->setUserAccountTotal(0);

        $course->registerStudent($studentWithRecommanderWithStock);

        //创建
        $upgradeUserOrder = $studentWithRecommanderWithStock->createUpgradeUserOrder(UserLevel::ADVANCED);
        $this->assertEquals(UserLevel::$userLevelPriceArray[UserLevel::ADVANCED], $upgradeUserOrder->getTotal());
        $this->assertTrue($upgradeUserOrder->isCreated());

        //处理中
        $upgradeUserOrder->addPayment(3000, '第一笔钱');
        $this->assertTrue($upgradeUserOrder->isPending());

        //升级通过去分钱
        $upgradeUserOrder->setApproved();
        $this->assertTrue($upgradeUserOrder->isApproved());

        //讲师账户
        $this->assertEquals(1, $teacher->getUser()->getUserAccountOrders()->count());
        $teacherAccountOrder = $teacher->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$teacherRewards[Subject::THINKING][UserLevel::ADVANCED], $teacherAccountOrder->getAmount());
        $this->assertTrue(!$teacherAccountOrder->isPaid());
        $this->assertEquals($teacherAccountOrder->getAmount(), $teacher->getUser()->getUserAccountTotal());

        //有库存推荐人账户
        $this->assertEquals(1, $recommanderWithStock->getUserAccountOrders()->count());
        $recommanderWithStockAccountOrder = $recommanderWithStock->getUserAccountOrders()[0];
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::ADVANCED], $recommanderWithStockAccountOrder->getAmount());
        $this->assertTrue(!$recommanderWithStockAccountOrder->isPaid());
        $this->assertEquals($stock - 1, $recommanderWithStock->getRecommandStock());
        $this->assertEquals($recommanderWithStockAccountOrder->getAmount(), $recommanderWithStock->getUserAccountTotal());



        //3. 无库存推荐人学员升级，分钱给最近讲师
        $recommanderWithStock->setUserAccountTotal(0);
        $teacher->getUser()->setUserAccountTotal(0);
        $teacher->getUser()->setUserAccountOrders(new ArrayCollection());

        $course->registerStudent($studentWithRecommanderWithoutStock);

        //创建
        $upgradeUserOrder = $studentWithRecommanderWithoutStock->createUpgradeUserOrder(UserLevel::ADVANCED);
        $this->assertEquals(UserLevel::$userLevelPriceArray[UserLevel::ADVANCED], $upgradeUserOrder->getTotal());
        $this->assertTrue($upgradeUserOrder->isCreated());

        //处理中
        $upgradeUserOrder->addPayment(3000, '第一笔钱');
        $this->assertTrue($upgradeUserOrder->isPending());

        //升级通过去分钱
        $upgradeUserOrder->setApproved();
        $this->assertTrue($upgradeUserOrder->isApproved());

        //讲师账户
        $this->assertEquals(1, $teacher->getUser()->getUserAccountOrders()->count());
        $teacherAccountOrder = $teacher->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$teacherRewards[Subject::THINKING][UserLevel::ADVANCED], $teacherAccountOrder->getAmount());
        $this->assertTrue(!$teacherAccountOrder->isPaid());
        $this->assertEquals($teacherAccountOrder->getAmount(), $teacher->getUser()->getUserAccountTotal());

        //有库存推荐人账户
        $this->assertEquals(0, $recommanderWithoutStock->getUserAccountOrders()->count());
        $this->assertEquals(0, $recommanderWithoutStock->getRecommandStock());
        $this->assertEquals(0, $recommanderWithoutStock->getUserAccountTotal());

    }


    /**
     * 测试交易会上学员升级合伙人 (上过思维课）
     */
    public function testTradingUpgradeAdvanced() {
        $student = parent::createStudent(UserLevel::VISITOR);
        $recommander = parent::createStudent(UserLevel::PARTNER);
        $recommander->setRecommandStock(100);
        $student->setParentUser($recommander);

        $teacherThinking = parent::createTeacher();
        $price = 380;
        $courseThinking = parent::createCourse($teacherThinking, Subject::THINKING, $price);

        $teacherTrading = parent::createTeacher();
        $price = 390;
        $courseTrading = parent::createCourse($teacherTrading, Subject::TRADING, $price);

        $courseTrading->registerStudent($student);
        $courseThinking->registerStudent($student);

        $upgradeUserOrder = $student->createUpgradeUserOrder(UserLevel::PARTNER);
        $upgradeUserOrder->setApproved();

        //会员等级变化,有推荐资格了
        $this->assertTrue($student->isPartnerUser());
        $this->assertEquals(UserLevel::$userLevelRecommanderStockArray[UserLevel::PARTNER], $student->getRecommandStock());

        //推荐人得钱
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $recommander->getUserAccountTotal());
        $this->assertEquals(1, $recommander->getUserAccountOrders()->count());
        $recommanderUserAccountOrder = $recommander->getUserAccountOrders()[0];
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $recommanderUserAccountOrder->getAmount());
        $this->assertTrue(!$recommanderUserAccountOrder->isPaid());

        //系统课讲师得钱(直接）
        $this->assertEquals(Subject::$teacherRewards[Subject::TRADING][UserLevel::PARTNER], $teacherTrading->getUser()->getUserAccountTotal());
        $this->assertEquals(1, $teacherTrading->getUser()->getUserAccountOrders()->count());
        $teacherTradingUserAccountOrder = $teacherTrading->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$teacherRewards[Subject::THINKING][UserLevel::PARTNER], $teacherTradingUserAccountOrder->getAmount());
        $this->assertTrue(!$teacherTradingUserAccountOrder->isPaid());

        //思维课讲师得钱（间接）
        $this->assertEquals($student->getLatestCourse(Subject::THINKING), $courseThinking);
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::TRADING][Subject::THINKING][UserLevel::PARTNER], $teacherThinking->getUser()->getUserAccountTotal());
        $this->assertEquals(1, $teacherThinking->getUser()->getUserAccountOrders()->count());
        $teacherThinkingUserAccountOrder = $teacherThinking->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::TRADING][Subject::THINKING][UserLevel::PARTNER], $teacherThinkingUserAccountOrder->getAmount());
        $this->assertTrue(!$teacherThinkingUserAccountOrder->isPaid());

    }


    /**
     * 测试系统上学员升级合伙人 (上过思维课）
     */
    public function testSystemUpgradePartner() {
        $student = parent::createStudent(UserLevel::VISITOR);
        $recommander = parent::createStudent(UserLevel::PARTNER);
        $recommander->setRecommandStock(100);
        $student->setParentUser($recommander);

        $teacherThinking = parent::createTeacher();
        $price = 380;
        $courseThinking = parent::createCourse($teacherThinking, Subject::THINKING, $price);

        $teacherTrading = parent::createTeacher();
        $price = 390;
        $courseTrading = parent::createCourse($teacherTrading, Subject::TRADING, $price);

        $teacherSystem = parent::createTeacher();
        $price = 500;
        $courseSystem = parent::createCourse($teacherSystem, Subject::SYSTEM_1, $price);

        $courseSystem->registerStudent($student);
        $courseTrading->registerStudent($student);
        $courseThinking->registerStudent($student);


        $upgradeUserOrder = $student->createUpgradeUserOrder(UserLevel::PARTNER);
        $upgradeUserOrder->setApproved();

        //会员等级变化,有推荐资格了
        $this->assertTrue($student->isPartnerUser());
        $this->assertEquals(UserLevel::$userLevelRecommanderStockArray[UserLevel::PARTNER], $student->getRecommandStock());

        //推荐人得钱
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $recommander->getUserAccountTotal());
        $this->assertEquals(1, $recommander->getUserAccountOrders()->count());
        $recommanderUserAccountOrder = $recommander->getUserAccountOrders()[0];
        $this->assertEquals(UserLevel::$userLevelRecommanderRewardsArray[UserLevel::PARTNER], $recommanderUserAccountOrder->getAmount());
        $this->assertTrue(!$recommanderUserAccountOrder->isPaid());

        //系统课讲师得钱(直接）
        $this->assertEquals(Subject::$teacherRewards[Subject::SYSTEM_1][UserLevel::PARTNER], $teacherSystem->getUser()->getUserAccountTotal());
        $this->assertEquals(1, $teacherSystem->getUser()->getUserAccountOrders()->count());
        $teacherSystemUserAccountOrder = $teacherSystem->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$teacherRewards[Subject::SYSTEM_1][UserLevel::PARTNER], $teacherSystemUserAccountOrder->getAmount());
        $this->assertTrue(!$teacherSystemUserAccountOrder->isPaid());
        $this->assertEquals($upgradeUserOrder, $teacherSystemUserAccountOrder->getUpgradeUserOrder());

        //交易课讲师得钱（间接）
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::SYSTEM_1][Subject::TRADING][UserLevel::PARTNER], $teacherTrading->getUser()->getUserAccountTotal());
        $this->assertEquals(1, $teacherTrading->getUser()->getUserAccountOrders()->count());
        $teacherTradingUserAccountOrder = $teacherTrading->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::TRADING][Subject::THINKING][UserLevel::PARTNER], $teacherTradingUserAccountOrder->getAmount());
        $this->assertTrue(!$teacherTradingUserAccountOrder->isPaid());
        $this->assertEquals($upgradeUserOrder, $teacherTradingUserAccountOrder->getUpgradeUserOrder());

        //思维课讲师得钱（间接）
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::SYSTEM_1][Subject::THINKING][UserLevel::PARTNER], $teacherThinking->getUser()->getUserAccountTotal());
        $this->assertEquals(1, $teacherThinking->getUser()->getUserAccountOrders()->count());
        $teacherThinkingUserAccountOrder = $teacherThinking->getUser()->getUserAccountOrders()[0];
        $this->assertEquals(Subject::$oldTeacherRewards[Subject::TRADING][Subject::THINKING][UserLevel::PARTNER], $teacherThinkingUserAccountOrder->getAmount());
        $this->assertTrue(!$teacherThinkingUserAccountOrder->isPaid());
        $this->assertEquals($upgradeUserOrder, $teacherThinkingUserAccountOrder->getUpgradeUserOrder());
    }

    /**
     * 测试提现操作
     */
    public function testWithdrawUserAccountOrder() {
        $totalAmount = 100;
        $withdrawAmount = 10;
        $student = parent::createStudent(UserLevel::PARTNER);
        $student->setUserAccountTotal($totalAmount);

        $withdrawOrder = $student->createUserAccountOrder(UserAccountOrder::WITHDRAW, $withdrawAmount);

        $this->assertFalse($withdrawOrder->isPaid());
        $this->assertEquals($withdrawAmount, $student->getWithDrawingTotal());
        $this->assertEquals($totalAmount-$withdrawAmount, $student->getUserAccountTotal());

        $withdrawOrder->setPaid();

        $this->assertEquals($totalAmount-$withdrawAmount, $student->getUserAccountTotal());
        $this->assertEquals(0, $student->getWithDrawingTotal());
        $this->assertEquals($withdrawAmount, $student->getWithDrawedTotal());
    }

    /**
     * 测试更新推荐人
     */
    public function testSetParent() {

        //下线无推荐人，进入推荐人链接， 推荐人是合伙人
        $child = $this->createStudent(UserLevel::VISITOR);
        $parent = $this->createStudent(UserLevel::PARTNER);

        $parentShareSource = $this->createShareSource($parent);
        $parent->addShareSource($parentShareSource);
        $shareSourceUser = ShareSourceUser::factory($parentShareSource, $child);
        $parentShareSource->addShareSourceUser($shareSourceUser);

        $this->assertEquals($parent, $child->getParentUser());
        $this->assertEquals($child, $parent->getSubUsers()[0]);

        //下线无推荐人，进入推荐人链接， 推荐人不是合伙人
        $child = $this->createStudent(UserLevel::VISITOR);
        $parent = $this->createStudent(UserLevel::ADVANCED);

        $parentShareSource = $this->createShareSource($parent);
        $parent->addShareSource($parentShareSource);
        $shareSourceUser = ShareSourceUser::factory($parentShareSource, $child);
        $parentShareSource->addShareSourceUser($shareSourceUser);

        $this->assertEquals(null, $child->getParentUser());
        $this->assertEquals(0, $parent->getSubUsers()->count());

        //下线有其他推荐人未过期，进入推荐人链接， 推荐人是合伙人
        $child = $this->createStudent(UserLevel::VISITOR);
        $parent = $this->createStudent(UserLevel::PARTNER);
        $parent2 = $this->createStudent(UserLevel::PARTNER);
        $child->setParentUser($parent2);
        $parent2->addSubUser($child);

        $parentShareSource = $this->createShareSource($parent);
        $parent->addShareSource($parentShareSource);
        $shareSourceUser = ShareSourceUser::factory($parentShareSource, $child);
        $parentShareSource->addShareSourceUser($shareSourceUser);

        $this->assertEquals($parent2, $child->getParentUser());
        $this->assertEquals(0, $parent->getSubUsers()->count());
        $this->assertEquals($child, $parent2->getSubUsers()[0]);

        //下线有其他推荐人已过期，进入推荐人链接， 推荐人是和合伙人
        $child = $this->createStudent(UserLevel::VISITOR);
        $child->setName('child');
        $parent2 = $this->createStudent(UserLevel::PARTNER);
        $parent2->setName('old');
        $parent2->addSubUser($child);

        $child->setParentUserExpiresAt(time() - User::PARENT_EXPIRES_SECONDS - 3600);

        $parent = $this->createStudent(UserLevel::PARTNER);
        $parent->setName('new');
        $parentShareSource = $this->createShareSource($parent);
        $parent->addShareSource($parentShareSource);

        $shareSourceUser = ShareSourceUser::factory($parentShareSource, $child);
        $parentShareSource->addShareSourceUser($shareSourceUser);

        $this->assertEquals($parent, $child->getParentUser());
        $this->assertEquals($child, $parent->getSubUsers()[0]);
        $this->assertEquals(0, $parent2->getSubUsers()->count());
    }
}