<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-04
 * Time: 2:25 PM
 */

namespace App\Tests\Jinqiu;


use App\Entity\BianxianUserLevel;
use App\Entity\CourseOrder;
use App\Entity\GroupGiftOrder;
use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\ShareSourceUser;
use App\Entity\Subject;
use App\Entity\UpgradeUserOrder;
use App\Entity\UpgradeUserOrderPayment;
use App\Entity\UserLevel;
use App\Entity\UserParentLog;
use App\Entity\User;

class BusinessLogic extends JinqiuBaseTestCase
{
    /**
     * 测试集call流程
     * 1. 创建开团订单 GroupOrder created, GroupUserOrder created, paid
     * 2. 加入团购订单 GroupUserOrder.created, unpaid => created, paid
     * 3. 完成拼团订单 GroupOrder.completed, GroupUserOrder.pending
     * 4. 所有课程注册
     *
     * @throws \Exception
     */
    public function testCreateGroupOrder() {

        $teacher = $this->createTeacher();
        $course = $this->createCourse($teacher);
        $totalGroupUserOrdersRequired = random_int(2, 20);
        echo "groupUserOrder required =  " . $totalGroupUserOrdersRequired . " \n";

        $product = $course->getProduct();
        $product->setTotalGroupUserOrdersRequired($totalGroupUserOrdersRequired);

        //create group order
        $masterUser = $this->createUser();
        $groupOrder = GroupOrder::factory(GroupOrder::GROUP_GIFT, $masterUser, $product);
        $this->assertTrue($groupOrder instanceof GroupGiftOrder);
        $this->assertEquals($totalGroupUserOrdersRequired, $groupOrder->getTotalGroupUserOrdersRequired());
        $this->assertTrue($groupOrder->isCreated());
        $this->assertEquals($masterUser, $groupOrder->getUser());
        $this->assertEquals(1, $groupOrder->getTotalGroupUserOrders());

        //master order
        $masterGroupUserOrder = $groupOrder->getMasterGroupUserOrder();
        $this->assertEquals($groupOrder, $masterGroupUserOrder->getGroupOrder());
        $this->assertTrue($masterGroupUserOrder instanceof CourseOrder);
        $this->assertTrue($masterGroupUserOrder->isCreated());
        $this->assertTrue($masterGroupUserOrder->isUnPaid());

        //group order pending
        $groupOrder->setPending();
        $this->assertTrue($groupOrder->isPending());
        $this->assertTrue($masterGroupUserOrder->isPaid());
        $this->assertEquals(0, $course->getTotalCourseStudents());

        $totalSlaveOrdersRequired = $groupOrder->getRestGroupUserOrdersRequired();
        for ($i = 1; $i <= $totalSlaveOrdersRequired; $i++) {
            $slaveUser = $this->createUser();
            $slaveGroupUserOrder = GroupUserOrder::factory($slaveUser, $product, $groupOrder);
            $this->assertTrue($slaveGroupUserOrder instanceof  CourseOrder);
            $this->assertTrue($slaveGroupUserOrder->isCreated());
            $this->assertTrue($slaveGroupUserOrder->isUnPaid());

            //join group
            $slaveGroupUserOrder->setPaid();

            $this->assertTrue($slaveGroupUserOrder->isPaid());
            $this->assertEquals($groupOrder, $slaveGroupUserOrder->getGroupOrder());

            if ($groupOrder->getRestGroupUserOrdersRequired() == 0) {
                $this->assertEquals(0, $groupOrder->getRestGroupUserOrdersRequired());
                $this->assertTrue($slaveGroupUserOrder->isRegistered());
            } else {
                $this->assertEquals($slaveGroupUserOrder->getStatus(), GroupUserOrder::CREATED);
            }
        }

        $this->assertEquals(0, $groupOrder->getRestGroupUserOrdersRequired());

        foreach ($groupOrder->getGroupUserOrders() as $groupUserOrder) {
            $this->assertTrue($groupUserOrder instanceof CourseOrder);
            $this->assertTrue($groupUserOrder->isRegistered());
        }

        $this->assertEquals($totalGroupUserOrdersRequired, $course->getTotalCourseStudents());
    }

    /**
     * 测试用户升级订单 (合伙人 = 推荐人）
     * 1. 创建UpgradeUserOrder, pending, GroupUserOrder.created, unpaid
     * 2. 支付完成, GroupUserOrder.pending, paid => UpgradeUserOrder.approved
     * 3. 用户升级成高级会员
     * 4. 分钱流程
     * 4.1 找到普通会员的推荐人，如果是高级会员没有名额则继续往上线找只到有名额的合伙人
     * 4.2 分配推荐人600
     * 4.3 分配合伙人400
     * 4.4 分配变现讲师100 （通过接口实现）
     * 4.5 分配供货商 (这个根据产品利润走)
     *
     */
    public function testCreateUpgradeUserOrderWithSameRoles() {

        $user = $this->createUser();
        $user->setUserLevel(UserLevel::VISITOR);

        $recommander = $this->createUser();
        $recommander->setUserLevel(UserLevel::ADVANCED);

        $partner = $recommander;
        $partner->setUserLevel(UserLevel::PARTNER);

        $partnerTeacher = $this->createTeacher()->getUser();
        $partnerTeacher->setUserLevel(UserLevel::PARTNER_TEACHER);

        $partner->setRecommandStock(1000);

        $supplier = $this->createUser();
        $supplier->setUserLevel(UserLevel::VISITOR);

        $product = $this->createProduct();
        $product->setPrice(2000);
        $product->setSupplierPrice(400);
        $product->setSupplierUser($supplier);
        $this->assertEquals(1600, $product->getRegularOrderUnitProfit());

        $partner->setPartnerTeacherRecommanderUser($partnerTeacher);
        $recommander->setParentUser($partner);
        $user->setParentUser($recommander);

        $this->assertEquals($recommander, $user->getParentUser());
        $this->assertEquals($partner, $recommander->getTopParentPartnerUser());
        $this->assertEquals($partnerTeacher, $partner->getPartnerTeacherRecommanderUser());
        $this->assertEquals($partner, $user->getTopParentPartnerUser());

        $this->assertEquals(0, $user->getUserAccountTotal());
        $this->assertEquals(0, $supplier->getUserAccountTotal());
        $this->assertEquals(0, $recommander->getUserAccountTotal());
        $this->assertEquals(0, $partner->getUserAccountTotal());
        $this->assertEquals(0, $partnerTeacher->getUserAccountTotal());

        //用户创建了升级订单
        $groupUserOrder = GroupUserOrder::factory($user, $product);
        $upgradeUserOrder = $user->createUpgradeUserOrder(UpgradeUserOrder::JINQIU,UserLevel::ADVANCED, $groupUserOrder);
        $this->assertTrue($groupUserOrder->isCreated());
        $this->assertTrue($groupUserOrder->isUnPaid());
        $this->assertTrue($upgradeUserOrder->isCreated());

        $potentialUserAccountOrders = $upgradeUserOrder->getPotentialUserAccountOrders();
        $this->assertEquals(4, $potentialUserAccountOrders->count());
        foreach ($potentialUserAccountOrders as $userAccountOrder) {
            if ($userAccountOrder->isSupplierRewards()) {
                $this->assertEquals($product->getSupplierPrice(), $userAccountOrder->getAmount());
            } else if ($userAccountOrder->isRecommandRewards()) {
                $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::ADVANCED], $userAccountOrder->getAmount());
            } else if ($userAccountOrder->isPartnerRewards()) {
                $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER], $userAccountOrder->getAmount());
            } else if ($userAccountOrder->isPartnerTeacherRewards()) {
                $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER], $userAccountOrder->getAmount());
            }
        }

        $this->assertEquals(0, $user->getUserAccountTotal());
        $this->assertEquals(0, $supplier->getUserAccountTotal());
        $this->assertEquals(0, $recommander->getUserAccountTotal());
        $this->assertEquals(0, $partner->getUserAccountTotal());
        $this->assertEquals(0, $partnerTeacher->getUserAccountTotal());

        $this->assertEquals(1000, $partner->getRecommandStock());

        //用户支付了订单
        $groupUserOrder->setPaid();

        $this->assertTrue($groupUserOrder->isPending());
        $this->assertTrue($groupUserOrder->isPaid());
        $this->assertTrue($upgradeUserOrder->isApproved());

        $this->assertEquals(1, $upgradeUserOrder->getUpgradeUserOrderPayments()->count());
        $payment = $upgradeUserOrder->getUpgradeUserOrderPayments()[0];
        $this->assertEquals(2000, $payment->getAmount());

        $this->assertEquals(1, $supplier->getTotalUserAccountOrders());
        $supplierUserAccountOrder = $supplier->getUserAccountOrders()[0];
        $this->assertEquals($product->getSupplierPrice(), $supplierUserAccountOrder->getAmount());
        $this->assertTrue($supplierUserAccountOrder->isPaid());
        $this->assertEquals($product->getSupplierPrice(), $supplier->getUserAccountTotal());

        $this->assertEquals(2, $recommander->getTotalUserAccountOrders());
        $recommanderUserAccountOrder = $recommander->getUserAccountOrders()[0];
        $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::ADVANCED], $recommanderUserAccountOrder->getAmount());
        $this->assertTrue($recommanderUserAccountOrder->isPaid());

        $partnerUserAccountOrder = $recommander->getUserAccountOrders()[1];
        $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER], $partnerUserAccountOrder->getAmount());
        $this->assertTrue($partnerUserAccountOrder->isPaid());

        $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::ADVANCED] + UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER], $recommander->getUserAccountTotal());

        $this->assertEquals(1, $partnerTeacher->getTotalUserAccountOrders());
        $partnerTeacherUserAccountOrder = $partnerTeacher->getUserAccountOrders()[0];
        $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER], $partnerTeacherUserAccountOrder->getAmount());
        $this->assertTrue($partnerTeacherUserAccountOrder->isPaid());
        $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER], $partnerTeacher->getUserAccountTotal());

        $this->assertEquals(1000, $partner->getRecommandStock());
        $this->assertEquals(0, $partner->getTotalRecommandStockOrders());
        //$userStockOrder = $partner->getUserRecommandStockOrders()[0];
        //$this->assertEquals(-1, $userStockOrder->getQty());
    }


    /**
     * 测试用户升级订单 (合伙人 => 推荐人(高级VIP, 荣耀VIP, 特级VIP) => 用户）
     */
    public function testCreateUpgradeUserOrderWithDifferentRoles() {

        $testLevels = [UserLevel::ADVANCED, UserLevel::ADVANCED2, UserLevel::ADVANCED3];

        foreach ($testLevels as $testLevel) {
            $user = $this->createUser();
            $user->setUserLevel(UserLevel::VISITOR);

            $recommander = $this->createUser();
            $recommander->setUserLevel($testLevel);

            $partner = $this->createUser();
            $partner->setUserLevel(UserLevel::PARTNER);

            $partnerTeacher = $this->createTeacher()->getUser();
            $partnerTeacher->setUserLevel(UserLevel::PARTNER_TEACHER);

            $partner->setRecommandStock(1000);

            $supplier = $this->createUser();
            $supplier->setUserLevel(UserLevel::VISITOR);

            $product = $this->createProduct();
            $product->setPrice(2000);
            $product->setSupplierPrice(400);
            $product->setSupplierUser($supplier);
            $this->assertEquals(1600, $product->getRegularOrderUnitProfit());

            $partner->setPartnerTeacherRecommanderUser($partnerTeacher);
            $recommander->setParentUser($partner);
            $user->setParentUser($recommander);

            $this->assertEquals($recommander, $user->getParentUser());
            $this->assertEquals($partner, $recommander->getParentUser());
            $this->assertEquals($partnerTeacher, $partner->getPartnerTeacherRecommanderUser());
            $this->assertEquals($partner, $user->getTopParentPartnerUser());

            $this->assertEquals(0, $user->getUserAccountTotal());
            $this->assertEquals(0, $supplier->getUserAccountTotal());
            $this->assertEquals(0, $recommander->getUserAccountTotal());
            $this->assertEquals(0, $partner->getUserAccountTotal());
            $this->assertEquals(0, $partnerTeacher->getUserAccountTotal());

            //用户创建了升级订单
            $groupUserOrder = GroupUserOrder::factory($user, $product);
            $upgradeUserOrder = $user->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED, $groupUserOrder);
            $this->assertTrue($groupUserOrder->isCreated());
            $this->assertTrue($groupUserOrder->isUnPaid());
            $this->assertTrue($upgradeUserOrder->isCreated());

            $potentialUserAccountOrders = $upgradeUserOrder->getPotentialUserAccountOrders();
            $this->assertEquals(4, $potentialUserAccountOrders->count());
            foreach ($potentialUserAccountOrders as $userAccountOrder) {
                if ($userAccountOrder->isSupplierRewards()) {
                    $this->assertEquals($product->getSupplierPrice(), $userAccountOrder->getAmount());
                } else if ($userAccountOrder->isRecommandRewards()) {
                    $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[$testLevel], $userAccountOrder->getAmount());
                } else if ($userAccountOrder->isPartnerRewards()) {
                    $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER], $userAccountOrder->getAmount());
                } else if ($userAccountOrder->isPartnerTeacherRewards()) {
                    $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER], $userAccountOrder->getAmount());
                }
            }

            $this->assertEquals(0, $user->getUserAccountTotal());
            $this->assertEquals(0, $supplier->getUserAccountTotal());
            $this->assertEquals(0, $recommander->getUserAccountTotal());
            $this->assertEquals(0, $partner->getUserAccountTotal());
            $this->assertEquals(0, $partnerTeacher->getUserAccountTotal());

            $this->assertEquals(1000, $partner->getRecommandStock());

            //用户支付了订单
            $groupUserOrder->setPaid();

            $this->assertTrue($groupUserOrder->isPending());
            $this->assertTrue($groupUserOrder->isPaid());
            $this->assertTrue($upgradeUserOrder->isApproved());

            $this->assertEquals($recommander, $upgradeUserOrder->getRecommanderUser());
            $this->assertEquals($partner, $upgradeUserOrder->getPartnerUser());
            $this->assertEquals($partnerTeacher, $upgradeUserOrder->getPartnerTeacherUser());

            $this->assertEquals(1, $supplier->getTotalUserAccountOrders());
            $supplierUserAccountOrder = $supplier->getUserAccountOrders()[0];
            $this->assertEquals($product->getSupplierPrice(), $supplierUserAccountOrder->getAmount());
            $this->assertTrue($supplierUserAccountOrder->isPaid());
            $this->assertEquals($product->getSupplierPrice(), $supplier->getUserAccountTotal());

            $this->assertEquals(1, $recommander->getTotalUserAccountOrders());
            $recommanderUserAccountOrder = $recommander->getUserAccountOrders()[0];
            $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[$testLevel], $recommanderUserAccountOrder->getAmount());
            $this->assertTrue($recommanderUserAccountOrder->isPaid());
            $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[$testLevel], $recommander->getUserAccountTotal());

            $this->assertEquals(1, $partner->getTotalUserAccountOrders());
            $partnerUserAccountOrder = $partner->getUserAccountOrders()[0];
            $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER], $partnerUserAccountOrder->getAmount());
            $this->assertTrue($partnerUserAccountOrder->isPaid());
            $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER], $partner->getUserAccountTotal());

            $this->assertEquals(1, $partnerTeacher->getTotalUserAccountOrders());
            $partnerTeacherUserAccountOrder = $partnerTeacher->getUserAccountOrders()[0];
            $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER], $partnerTeacherUserAccountOrder->getAmount());
            $this->assertTrue($partnerTeacherUserAccountOrder->isPaid());
            $this->assertEquals(UserLevel::$advanceUserUpgradeRewardsArray[UserLevel::PARTNER_TEACHER], $partnerTeacher->getUserAccountTotal());

            $this->assertEquals(1000, $partner->getRecommandStock());
            $this->assertEquals(0, $partner->getTotalRecommandStockOrders());
            //$userStockOrder = $partner->getUserRecommandStockOrders()[0];
            //$this->assertEquals(-1, $userStockOrder->getQty());
        }
    }


    /**
     * 测试推荐关系：分享不改变推荐关系
     */
    public function testTwoAdvancedRecommander() {

        // advanced1 & advanced2 => vistor
        $user = $this->createUser();
        $recommanderAdvanced1 = $this->createUser();
        $recommanderAdvanced1->setUserLevel(UserLevel::ADVANCED);

        $recommanderAdvanced2 = $this->createUser();
        $recommanderAdvanced2->setUserLevel(UserLevel::ADVANCED);

        $shareSource1 = $this->createShareSource($recommanderAdvanced1);
        $shareSource2 = $this->createShareSource($recommanderAdvanced2);

        $shareSourceUser1 = ShareSourceUser::factory($shareSource1, $user);
        $this->assertEquals(null, $user->getParentUser());
        $this->assertEquals(0, $user->getUserParentLogs()->count());

        $shareSourceUser2 = ShareSourceUser::factory($shareSource2, $user);
        $this->assertEquals(null, $user->getParentUser());
        $this->assertEquals(0, $user->getUserParentLogs()->count());
    }


    /**
     * 测试推荐资格的金秋 UserLevel: advanced1,2,3, partner 或者 变现 BianxianUserLevel: advanced, partner, distributor
     */
    public function testecommanderRight() {
        $user = $this->createUser();
        $this->assertFalse($user->hasRecommandRight());

        foreach ([UserLevel::ADVANCED, UserLevel::ADVANCED2,
                     UserLevel::ADVANCED3] as $userLevel) {
            $recommander = $this->createUser();
            $recommander->setUserLevel($userLevel);
            $this->assertTrue($recommander->hasRecommandRight());
        }
        foreach ([BianxianUserLevel::ADVANCED, BianxianUserLevel::PARTNER,
                     BianxianUserLevel::DISTRIBUTOR] as $bianxianUserLevel) {
            $recommander = $this->createUser();
            $recommander->setBianxianUserLevel($bianxianUserLevel);
            $this->assertTrue($recommander->hasRecommandRight());
        }
    }


    /**
     * 测试锁定推荐关系和锁定讲师, 普通到高级
     */
    public function testRecommandRelations() {


        /**
         * 有推荐人， 报名思维课390， 锁定推荐人45天，更新锁定讲师
         */
        $partner = $this->createUser();
        $partner->setBianxianUserLevel(BianxianUserLevel::PARTNER);

        $recommander = $this->createUser();
        $recommander->setBianxianUserLevel(BianxianUserLevel::ADVANCED);
        $recommander->setParentUser($partner);

        $this->assertEquals($partner, $recommander->getBianxianTopParentPartnerUser());


        $user = $this->createUser();
        $recommanderShareSource = $this->createShareSource($recommander);
        ShareSourceUser::factory($recommanderShareSource, $user);

        $teacher = $this->createTeacher();
        $thinkingCourse = $this->createCourse($teacher, Subject::THINKING);

        $this->assertFalse($thinkingCourse->isOnline());

        $courseOrder = CourseOrder::factory($user, $thinkingCourse->getProduct());
        $courseOrder->setRegistered();

        $this->assertEquals($teacher->getUser(), $user->getTeacherRecommanderUser());
        $this->assertEquals($partner, $user->getParentUser());
        $this->assertTrue($user->getParentUserExpiresAt() > 0);
        $this->assertEquals(BianxianUserLevel::THINKING, $user->getBianxianUserLevel());
        $this->assertEquals(UserLevel::VIP, $user->getUserLevel());


        /**
         * 有推荐人， 报名系统课12500， 锁定推荐人365天，给合伙人分钱10000， 思维课讲师1000， 税收1000， 会务费500， 扣一个合伙人名额
         */

        $partner = $this->createUser();
        $partner->setBianxianUserLevel(BianxianUserLevel::PARTNER);
        $partner->setRecommandStock(100);
        $this->assertEquals(0, $partner->getTotalUserAccountOrders());

        $recommander = $this->createUser();
        $recommander->setBianxianUserLevel(BianxianUserLevel::ADVANCED);
        $recommander->setParentUser($partner);

        $user = $this->createUser();
        $thinkingTeacher = $this->createTeacher();
        $user->setTeacherRecommanderUser($thinkingTeacher->getUser());

        $recommanderShareSource = $this->createShareSource($recommander);
        ShareSourceUser::factory($recommanderShareSource, $user);
        $teacher = $this->createTeacher();

        $systemCourse = $this->createCourse($teacher, Subject::SYSTEM_1);
        $systemCourse->setPrice(12000);

        $courseOrder = CourseOrder::factory($user, $systemCourse->getProduct());
        $courseOrder->setRegistered();

        $this->assertEquals($thinkingTeacher->getUser(), $user->getTeacherRecommanderUser());
        $this->assertEquals($partner, $user->getParentUser());
        $this->assertTrue($user->getParentUserExpiresAt() > 0);

        $this->assertEquals(1, $partner->getTotalUserAccountOrders());
        $partnerAccountOrder = $partner->getUserAccountOrders()[0];
        $this->assertEquals(10000, $partnerAccountOrder->getAmount());
        $this->assertTrue($partnerAccountOrder->isPaid());

        $this->assertEquals(1, $partner->getTotalRecommandStockOrders());
        $stockOrder = $partner->getUserRecommandStockOrders()[0];
        $this->assertEquals(-1, $stockOrder->getQty());
        $this->assertEquals(99, $partner->getRecommandStock());

        $this->assertEquals(BianxianUserLevel::ADVANCED, $user->getBianxianUserLevel());
        $this->assertEquals(UserLevel::ADVANCED2, $user->getUserLevel());
        $this->assertEquals($partner, $user->getParentUser());


        /**
         * 有推荐人， 购买2000， 锁定推荐人365天
         */


        /**
         * 有推荐人， 购买12000， 锁定推荐人365天
         */

    }

    // 测试高级报名低级

}
