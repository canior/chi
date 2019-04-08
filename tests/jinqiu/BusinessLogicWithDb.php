<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-04-02
 * Time: 11:33 PM
 */

namespace App\Tests\Jinqiu;


use App\Entity\BianxianUserLevel;
use App\Entity\CourseStudent;
use App\Entity\GroupOrder;
use App\Entity\UpgradeOrderCoupon;
use App\Entity\UserAccountOrder;
use App\Entity\UserLevel;
use App\Entity\ShareSourceUser;
use App\Entity\CourseOrder;
use App\Entity\UserRecommandStockOrder;
use App\Repository\CourseStudentRepository;
use App\Entity\Subject;
use App\Entity\GroupUserOrder;

class BusinessLogicWithDb extends JinqiuBaseTestCase
{
    /* 测试分享人更新 */
    public function testUserSharer() {
        $recommander1 = $this->createUser(true);
        $user = $this->createUser(true);

        /* 测试自己分享给自己 */
        $selfShareSource = $this->createShareSource($user, null,null,true);
        $selfShareSourceUser = ShareSourceUser::factory($selfShareSource, $user);
        $selfShareSource->addShareSourceUser($selfShareSourceUser);
        $this->getEntityManager()->persist($selfShareSource);
        $this->getEntityManager()->flush();
        $this->assertNull($user->getLatestFromShareSource());
        $this->assertNull($user->getRecommanderName());

        /* 第一个分享 */
        $recommander1ShareSource = $this->createShareSource($recommander1, null, null, true);
        $recommander1ShareSourceUser = ShareSourceUser::factory($recommander1ShareSource, $user);
        $recommander1ShareSource->addShareSourceUser($recommander1ShareSourceUser);
        $this->getEntityManager()->persist($recommander1ShareSource);
        $this->getEntityManager()->flush();
        $this->assertEquals($recommander1->getDisplayName(), $user->getRecommanderName());
        $this->assertEquals($recommander1ShareSource, $user->getLatestFromShareSource());

        /* 第二个分享 */
        $recommander2 = $this->createUser(true);
        $recommander2ShareSource = $this->createShareSource($recommander2, null, null, true);
        $recommander2ShareSourceUser = ShareSourceUser::factory($recommander2ShareSource, $user);
        $recommander2ShareSource->addShareSourceUser($recommander2ShareSourceUser);
        $this->getEntityManager()->persist($recommander2ShareSource);
        $this->getEntityManager()->flush();

        $this->assertEquals($recommander2->getDisplayName(), $user->getRecommanderName());
        $this->assertEquals($recommander2ShareSource, $user->getLatestFromShareSource());

        /* 又分享给自己 */
        $selfShareSource->addShareSourceUser($selfShareSourceUser);
        $this->getEntityManager()->persist($selfShareSource);
        $this->getEntityManager()->flush();

        $this->assertEquals($recommander2->getDisplayName(), $user->getRecommanderName());
        $this->assertEquals($recommander2ShareSource, $user->getLatestFromShareSource());

    }

    public function testBusinessLogic() {

        /**
         * 推荐人$recommander， 报名思维课390， 成为VIP|思维课学员， 锁定推荐人partner 45天，更新锁定讲师
         */

        $partner = $this->createUser(true);
        $partner->setUserLevel(UserLevel::PARTNER);
        $partner->setBianxianUserLevel(BianxianUserLevel::PARTNER);
        $partner->setRecommandStock(100);

        $recommander = $this->createUser(true);
        $recommander->setUserLevel(UserLevel::ADVANCED3);
        $recommander->setBianxianUserLevel(BianxianUserLevel::ADVANCED);
        $recommander->setParentUser($partner);

        $this->assertEquals($partner, $recommander->getTopParentPartnerUser());
        $this->assertEquals($partner, $recommander->getBianxianTopParentPartnerUser());

        $user = $this->createUser(true);
        $recommanderShareSource = $this->createShareSource($recommander, null, null, true);
        $shareSourceUser = ShareSourceUser::factory($recommanderShareSource, $user);
        $recommanderShareSource->addShareSourceUser($shareSourceUser);
        $this->getEntityManager()->persist($recommanderShareSource);
        $this->getEntityManager()->flush();
        $this->assertEquals(UserLevel::VISITOR, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::VISITOR, $user->getBianxianUserLevel());
        $this->assertEquals(null, $user->getParentUser());

        $teacher = $this->createTeacher(true);
        $thinkingCourse = $this->createCourse($teacher, Subject::THINKING, true);

        $courseOrder = CourseOrder::factory($user, $thinkingCourse->getProduct());
        $courseOrder->setRegistered();
        $this->getEntityManager()->persist($courseOrder);
        $this->getEntityManager()->flush();

        $this->assertEquals($partner, $user->getParentUser());

        /**
         * @var CourseStudentRepository
         */
        $courseStudentRepository = $this->getEntityManager()->getRepository(CourseStudent::class);
        $courseStudent = $courseStudentRepository->findOneBy(['studentUser' => $user, 'course' => $thinkingCourse, 'status' => CourseStudent::REGISTERED]);
        $this->assertTrue($courseStudent != null);

        $this->assertEquals($teacher->getUser(), $user->getTeacherRecommanderUser());
        $this->assertNull($user->getPartnerTeacherRecommanderUser());

        /**
         * 有推荐人， 报名系统课12500，成为荣耀VIP|系统课学员，锁定推荐人recommander 365天，
         * 分钱： 给合伙人分钱10000， 思维课讲师1000， 税收1000， 会务费500， 扣1个合伙人名额
         */

        $partnerTeacher = $this->createTeacher(true);

        $partner = $this->createUser(true);
        $partner->setUserLevel(UserLevel::PARTNER);
        $partner->setBianxianUserLevel(BianxianUserLevel::PARTNER);
        $partner->setRecommandStock(100);
        $partner->setPartnerTeacherRecommanderUser($partnerTeacher->getUser());

        $recommander = $this->createUser(true);
        $recommander->setUserLevel(UserLevel::ADVANCED3);
        $recommander->setBianxianUserLevel(BianxianUserLevel::ADVANCED);
        $recommander->setParentUser($partner);

        $this->assertEquals($partner, $recommander->getTopParentPartnerUser());
        $this->assertEquals($partner, $recommander->getBianxianTopParentPartnerUser());

        $user = $this->createUser(true);
        $recommanderShareSource = $this->createShareSource($recommander, null, null, true);
        $shareSourceUser = ShareSourceUser::factory($recommanderShareSource, $user);
        $recommanderShareSource->addShareSourceUser($shareSourceUser);
        $this->getEntityManager()->persist($recommanderShareSource);
        $this->getEntityManager()->flush();
        $this->assertEquals(UserLevel::VISITOR, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::VISITOR, $user->getBianxianUserLevel());
        $this->assertEquals(null, $user->getParentUser());

        $teacher = $this->createTeacher(true);
        $thinkingWithSystemCourse = $this->createCourse($teacher, Subject::TRADING, true);
        $thinkingWithSystemCourse->getProduct()->setHasCoupon(true);
        $this->getEntityManager()->persist($thinkingWithSystemCourse);
        $this->getEntityManager()->flush();

        $courseOrder = CourseOrder::factory($user, $thinkingWithSystemCourse->getProduct());
        $courseOrder->setRegistered();
        $this->getEntityManager()->persist($courseOrder);
        $this->getEntityManager()->flush();

        $upgradeOrderCoupons = $this->getEntityManager()->getRepository(UpgradeOrderCoupon::class)->findAll();
        $this->assertEquals(5, count($upgradeOrderCoupons));

        $this->assertEquals($partner, $user->getParentUser());

        $courseStudentRepository = $this->getEntityManager()->getRepository(CourseStudent::class);
        $courseStudent = $courseStudentRepository->findOneBy(['studentUser' => $user, 'course' => $thinkingWithSystemCourse, 'status' => CourseStudent::REGISTERED]);
        $this->assertTrue($courseStudent != null);

        $this->assertEquals($teacher->getUser(), $user->getTeacherRecommanderUser());
        $this->assertNull($user->getPartnerTeacherRecommanderUser());

        $partnerUserAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=>$partner, 'userAccountOrderType' => UserAccountOrder::PARTNER_REWARDS]);
        $this->assertEquals(1, count($partnerUserAccountOrders));
        $this->assertEquals(10000, $partnerUserAccountOrders[0]->getAmount());

        $thinkingTeacherAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=>$teacher->getUser(), 'userAccountOrderType' => UserAccountOrder::TEACHER_REWARDS]);
        $this->assertEquals(1, count($thinkingTeacherAccountOrders));
        $this->assertEquals(1000, $thinkingTeacherAccountOrders[0]->getAmount());

        $partnerRecommandStockOrder = $this->getEntityManager()->getRepository(UserRecommandStockOrder::class)->findOneBy(['user' => $partner]);
        $this->assertNotNull($partnerRecommandStockOrder);
        $this->assertEquals(-1, $partnerRecommandStockOrder->getQty());

        $this->assertEquals(UserLevel::ADVANCED3, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::ADVANCED, $user->getBianxianUserLevel());


        /**
         * 有推荐人，购买2000，高级成为VIP|思维课学员，锁定推荐人365天, 给2000元产品
         * 分钱：合伙人 400， 推荐人 600， 供货商 500， 合伙人的讲师100
         */
        $supplierUser = $this->createUser(true);
        $partnerTeacher = $this->createTeacher(true);

        $partner = $this->createUser(true);
        $partner->setUserLevel(UserLevel::PARTNER);
        $partner->setBianxianUserLevel(BianxianUserLevel::PARTNER);
        $partner->setRecommandStock(100);
        $partner->setPartnerTeacherRecommanderUser($partnerTeacher->getUser());

        $recommander = $this->createUser(true);
        $recommander->setUserLevel(UserLevel::ADVANCED3);
        $recommander->setBianxianUserLevel(BianxianUserLevel::ADVANCED);
        $recommander->setParentUser($partner);

        $this->assertEquals($partner, $recommander->getTopParentPartnerUser());
        $this->assertEquals($partner, $recommander->getBianxianTopParentPartnerUser());

        $user = $this->createUser(true);
        $recommanderShareSource = $this->createShareSource($recommander, null, null, true);
        $shareSourceUser = ShareSourceUser::factory($recommanderShareSource, $user);
        $recommanderShareSource->addShareSourceUser($shareSourceUser);
        $this->getEntityManager()->persist($recommanderShareSource);
        $this->getEntityManager()->flush();

        $this->assertEquals(UserLevel::VISITOR, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::VISITOR, $user->getBianxianUserLevel());
        $this->assertEquals(null, $user->getParentUser());

        $product = $this->createProduct(true);
        $product->setSupplierUser($supplierUser);
        $product->setPrice(2000);
        $product->setSupplierUser($supplierUser);
        $product->setSupplierPrice(400);
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();

        $groupUserOrder = GroupUserOrder::factory($user, $product);
        $groupUserOrder->setPaid();

        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        $this->assertEquals($recommander, $user->getParentUser());
        $this->assertNull($user->getTeacherRecommanderUser());
        $this->assertNull($user->getPartnerTeacherRecommanderUser());

        $recommanderUserAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $recommander, 'userAccountOrderType' => UserAccountOrder::RECOMMAND_REWARDS]);
        $this->assertEquals(1, count($recommanderUserAccountOrders));
        $this->assertEquals(600, $recommanderUserAccountOrders[0]->getAmount());

        $partnerUserAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $partner, 'userAccountOrderType' => UserAccountOrder::PARTNER_REWARDS]);
        $this->assertEquals(1, count($partnerUserAccountOrders));
        $this->assertEquals(400, $partnerUserAccountOrders[0]->getAmount());

        $partnerTeacherAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $partnerTeacher->getUser(), 'userAccountOrderType' => UserAccountOrder::PARTNER_TEACHER_REWARDS]);
        $this->assertEquals(1, count($partnerTeacherAccountOrders));
        $this->assertEquals(100, $partnerTeacherAccountOrders[0]->getAmount());

        $supplierAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $supplierUser, 'userAccountOrderType' => UserAccountOrder::SUPPLIER_REWARDS]);
        $this->assertEquals(1, count($supplierAccountOrders));
        $this->assertEquals(400, $supplierAccountOrders[0]->getAmount());

        $partnerRecommandStockOrder = $this->getEntityManager()->getRepository(UserRecommandStockOrder::class)->findOneBy(['user' => $partner]);
        $this->assertNull($partnerRecommandStockOrder);

        $this->assertEquals(UserLevel::ADVANCED, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::THINKING, $user->getBianxianUserLevel());


        /**
         * 有推荐人， 购买12000， 成为荣耀VIP|系统课学员， 锁定推荐人recommander 365天, 给5个特权vip升级码，10000元产品
         * 分钱：合伙人 400， 推荐人 600， 供货商 500*6 = 3000， 合伙人的讲师100
         */
        $supplierUser = $this->createUser(true);
        $partnerTeacher = $this->createTeacher(true);

        $partner = $this->createUser(true);
        $partner->setUserLevel(UserLevel::PARTNER);
        $partner->setBianxianUserLevel(BianxianUserLevel::PARTNER);
        $partner->setRecommandStock(100);
        $partner->setPartnerTeacherRecommanderUser($partnerTeacher->getUser());

        $recommander = $this->createUser(true);
        $recommander->setUserLevel(UserLevel::ADVANCED3);
        $recommander->setBianxianUserLevel(BianxianUserLevel::ADVANCED);
        $recommander->setParentUser($partner);

        $this->assertEquals($partner, $recommander->getTopParentPartnerUser());
        $this->assertEquals($partner, $recommander->getBianxianTopParentPartnerUser());

        $user = $this->createUser(true);
        $recommanderShareSource = $this->createShareSource($recommander, null, null, true);
        $shareSourceUser = ShareSourceUser::factory($recommanderShareSource, $user);
        $recommanderShareSource->addShareSourceUser($shareSourceUser);
        $this->getEntityManager()->persist($recommanderShareSource);
        $this->getEntityManager()->flush();

        $this->assertEquals(UserLevel::VISITOR, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::VISITOR, $user->getBianxianUserLevel());
        $this->assertEquals(null, $user->getParentUser());

        $product = $this->createProduct(true);
        $product->setSupplierUser($supplierUser);
        $product->setPrice(12000);
        $product->setSupplierUser($supplierUser);
        $product->setSupplierPrice(2400);
        $product->setHasCoupon(true);
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();

        $groupUserOrder = GroupUserOrder::factory($user, $product);
        $groupUserOrder->setPaid();

        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        $this->assertEquals($recommander, $user->getParentUser());
        $this->assertNull($user->getTeacherRecommanderUser());
        $this->assertNull($user->getPartnerTeacherRecommanderUser());

        $recommanderUserAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $recommander, 'userAccountOrderType' => UserAccountOrder::RECOMMAND_REWARDS]);
        $this->assertEquals(1, count($recommanderUserAccountOrders));
        $this->assertEquals(600, $recommanderUserAccountOrders[0]->getAmount());

        $partnerUserAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $partner, 'userAccountOrderType' => UserAccountOrder::PARTNER_REWARDS]);
        $this->assertEquals(1, count($partnerUserAccountOrders));
        $this->assertEquals(400, $partnerUserAccountOrders[0]->getAmount());

        $partnerTeacherAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $partnerTeacher->getUser(), 'userAccountOrderType' => UserAccountOrder::PARTNER_TEACHER_REWARDS]);
        $this->assertEquals(1, count($partnerTeacherAccountOrders));
        $this->assertEquals(100, $partnerTeacherAccountOrders[0]->getAmount());

        $supplierAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $supplierUser, 'userAccountOrderType' => UserAccountOrder::SUPPLIER_REWARDS]);
        $this->assertEquals(1, count($supplierAccountOrders));
        $this->assertEquals(2400, $supplierAccountOrders[0]->getAmount());

        $partnerRecommandStockOrder = $this->getEntityManager()->getRepository(UserRecommandStockOrder::class)->findOneBy(['user' => $partner]);
        $this->assertNull($partnerRecommandStockOrder);

        $this->assertEquals(UserLevel::ADVANCED3, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::ADVANCED, $user->getBianxianUserLevel());


        /**
         * 输入升级码，成为荣耀VIP|系统课学员， 锁定升级码的用户作为推荐人365天
         * 分钱：合伙人 400， 升级码用户（推荐人） 600， 合伙人的讲师100
         */
        $user = $this->createUser(true);
        $upgradeOrderCoupon = $this->getEntityManager()->getRepository(UpgradeOrderCoupon::class)->findOneBy(['groupUserOrder' => $groupUserOrder]);
        $upgradeOrderCoupon->setApproved($user);
        $this->getEntityManager()->persist($upgradeOrderCoupon);
        $this->getEntityManager()->flush();

        $this->assertEquals($user, $upgradeOrderCoupon->getUpgradeUser());
        $owner = $upgradeOrderCoupon->getGroupUserOrder()->getUser();
        $this->assertEquals($owner, $user->getParentUser());

        $partnerUserAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $partner, 'userAccountOrderType' => UserAccountOrder::PARTNER_REWARDS]);
        $this->assertEquals(2, count($partnerUserAccountOrders));
        $this->assertEquals(400, $partnerUserAccountOrders[1]->getAmount());

        $partnerTeacherAccountOrders = $this->getEntityManager()->getRepository(UserAccountOrder::class)->findBy(['paymentStatus' => UserAccountOrder::PAID, 'user'=> $partnerTeacher->getUser(), 'userAccountOrderType' => UserAccountOrder::PARTNER_TEACHER_REWARDS]);
        $this->assertEquals(2, count($partnerTeacherAccountOrders));
        $this->assertEquals(100, $partnerTeacherAccountOrders[1]->getAmount());

        $this->assertEquals(UserLevel::ADVANCED2, $user->getUserLevel());
        $this->assertEquals(BianxianUserLevel::THINKING, $user->getBianxianUserLevel());
    }

    public function testCreateGroupOrder() {
        $recommander = $this->createUser(true);
        $recommander->setUserLevel(UserLevel::PARTNER);
        $recommander->setBianxianUserLevel(BianxianUserLevel::PARTNER);

        $product = $this->createProduct(true);
        $teacher = $this->createTeacher(true);
        $course = $this->createCourse($teacher, Subject::THINKING, true);
        $product->setTotalGroupUserOrdersRequired(2);
        $product->setCourse($course);
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();

        $shareSource = $this->createShareSource($recommander, null, null,true);
        $master = $this->createUser(true);
        $masterShareSourceUser = ShareSourceUser::factory($shareSource, $master);
        $this->getEntityManager()->persist($masterShareSourceUser);
        $this->getEntityManager()->flush();

        $this->assertEquals($recommander->getDisplayName(), $master->getDisplayRecommanderName());
        $this->assertNull($master->getParentUser());
        $this->assertNull($master->getParentUserExpiresAt());

        $groupOrder = GroupOrder::factory(GroupOrder::GROUP_GIFT, $master, $product);
        $this->assertTrue($groupOrder->isCreated());

        $groupOrder->setPending();

        $this->getEntityManager()->persist($groupOrder);
        $this->getEntityManager()->flush();

        $this->assertTrue($groupOrder->getMasterGroupUserOrder()->isPending());
        $this->assertTrue($groupOrder->getMasterGroupUserOrder()->isPaid());

        $this->assertEquals($recommander->getDisplayName(), $master->getDisplayRecommanderName());
        $this->assertNull($master->getParentUser());
        $this->assertNull($master->getParentUserExpiresAt());

        $slave = $this->createUser(true);
        $masterShareSource = $this->createShareSource($master, null, null,true);
        $slaveShareSourceUser = ShareSourceUser::factory($masterShareSource, $slave);
        $this->getEntityManager()->persist($slaveShareSourceUser);
        $this->getEntityManager()->flush();

        $this->assertEquals($master->getDisplayName(), $slave->getDisplayRecommanderName());
        $this->assertNull($slave->getParentUser());
        $this->assertNull($slave->getParentUserExpiresAt());

        $groupUserOrder = GroupUserOrder::factory($slave, $product, $groupOrder);
        $groupUserOrder->setPaid();
        $this->getEntityManager()->persist($groupUserOrder);
        $this->getEntityManager()->flush();

        $this->assertTrue($groupOrder->isCompleted());
        $this->assertTrue($groupOrder->getMasterGroupUserOrder()->isPaid());
        $this->assertTrue($groupOrder->getMasterGroupUserOrder()->isDelivered());
        $this->assertTrue($groupOrder->getSlaveGroupUserOrder($slave)->isDelivered());
        $this->assertTrue($groupOrder->getSlaveGroupUserOrder($slave)->isPaid());

        $this->assertEquals($master->getDisplayName(), $slave->getDisplayRecommanderName());
        $this->assertNull($slave->getParentUser());
        $this->assertNull($slave->getParentUserExpiresAt());

    }

}
