<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-03
 * Time: 12:28 AM
 */

namespace App\Tests\Bianxian;


use App\Entity\CommandMessage;
use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Tests\BaseTestCase;

class BusinessLogicTest extends BaseTestCase
{

    /**
     * 自然流量进来的正常开团流程
     * 1. 团长开团，支付，分享
     * 2. 团员参团，支付
     * 3. 团员收货
     */
    public function testCompletedGroupOrder() {
        $product = $this->createProduct();
        $oldStock = $product->getStock();
        $captain = $this->createUser();

        //1. 创建拼团订单及团长客户订单
        $groupOrder = new GroupOrder($captain, $product);
        $groupOrder->setId(rand());
        $this->assertTrue($groupOrder->isCreated());

        $masterGroupUserOrder = $groupOrder->getMasterGroupUserOrder();
        $this->assertTrue($masterGroupUserOrder->isCreated());
        $this->assertFalse($masterGroupUserOrder->isPaid());
        $this->assertTrue($masterGroupUserOrder->isGroupOrder());
        $this->assertTrue($masterGroupUserOrder->isMasterOrder());
        $this->assertEquals($product->getGroupPrice(), $masterGroupUserOrder->getTotal());
        $this->assertEquals($product->getGroupOrderRewards(), $masterGroupUserOrder->getOrderRewards());
        $this->assertTrue($captain->getCommandMessages()->count() == 0);

        //2. 团长支付成功
        $groupOrder->setPending();

        $this->assertTrue($groupOrder->isPending());
        $this->assertTrue($masterGroupUserOrder->isPaid());
        $this->assertEquals($oldStock - 2, $product->getStock());

        $captainStatistics = $captain->getUserStatistics()[0];
        $this->assertEquals($captainStatistics->getGroupOrderNum(), 1);
        $this->assertEquals(0, $captain->getPendingTotalRewards());
        $this->assertEquals(0, $captainStatistics->getOrderRewardsTotal());
        $this->assertEquals(1, $captainStatistics->getGroupUserOrderNum());

        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createNotifyPendingGroupOrderCommand($groupOrder)));
        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createNotifyExpiringGroupOrderCommand($groupOrder)));


        //3. 团长分享给团员
        $captainShareSource = $this->createShareSource($captain, $groupOrder, $product);

        //团员打开分享链接
        $joiner = $this->createUser();
        $shareSourceUser = ShareSourceUser::factory($captainShareSource, $joiner);
        $captainShareSource->addShareSourceUser($shareSourceUser);

        $this->assertEquals(1, $captainStatistics->getSharedNum());
        $this->assertNull($joiner->getParentUser()); //此时团员的有效上线为空

        //4. 团员创建拼团订单
        $slaveGroupUserOrder = GroupUserOrder::factory($joiner, $product, $groupOrder);

        $slaveGroupUserOrder->setGroupOrder($groupOrder);
        $slaveGroupUserOrder->setTotal($product->getGroupPrice());
        $groupOrder->addGroupUserOrder($slaveGroupUserOrder);

        $this->assertFalse($slaveGroupUserOrder->isMasterOrder());
        $this->assertEquals(2, $groupOrder->getGroupUserOrders()->count());
        $this->assertEquals($slaveGroupUserOrder, $groupOrder->getSlaveGroupUserOrder($joiner));
        $this->assertTrue($slaveGroupUserOrder->isCreated());
        $this->assertTrue(!$slaveGroupUserOrder->isPaid());
        $this->assertTrue($groupOrder->isPending());
        $this->assertEquals($product->getGroupPrice(), $slaveGroupUserOrder->getTotal());
        $this->assertEquals($product->getGroupOrderRewards(), $slaveGroupUserOrder->getOrderRewards());

        //5. 团员支付拼团订单
        $groupOrder->setCompleted($joiner);

        $this->assertTrue($groupOrder->isCompleted());
        $this->assertTrue($masterGroupUserOrder->isPending());
        $this->assertTrue($masterGroupUserOrder->isPaid());
        $this->assertTrue($slaveGroupUserOrder->isPending());
        $this->assertTrue($slaveGroupUserOrder->isPaid());

        $this->assertEquals($captain, $joiner->getParentUser()); //团员成为团长有效下线
        $this->assertNull($captain->getParentUser());

        $this->assertEquals(1, $captainStatistics->getChildrenNum());
        $this->assertEquals(1, $captainStatistics->getGroupOrderNum());
        $this->assertEquals(0, $captainStatistics->getGroupOrderJoinedNum());
        $this->assertEquals(1, $captainStatistics->getGroupUserOrderNum());
        $this->assertEquals($captain->getPendingTotalRewards(), $captainStatistics->getOrderRewardsTotal());

        $joinerStatistics = $joiner->getUserStatistics()[0];
        $this->assertEquals(0, $joinerStatistics->getChildrenNum());
        $this->assertEquals(1, $joinerStatistics->getGroupOrderJoinedNum());
        $this->assertEquals(1, $joinerStatistics->getGroupUserOrderNum());
        $this->assertEquals($joiner->getPendingTotalRewards(), $joinerStatistics->getOrderRewardsTotal());

        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createNotifyCompletedGroupOrderCommand($groupOrder)));
        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createSendOrderRewardsCommand($masterGroupUserOrder)));
        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createNotifyOrderRewardsSentCommand($masterGroupUserOrder)));

        $this->assertNotNull($joiner->getCommandMessage(CommandMessage::createNotifyOrderRewardsSentCommand($slaveGroupUserOrder)));
        $this->assertNotNull($joiner->getCommandMessage(CommandMessage::createSendOrderRewardsCommand($slaveGroupUserOrder)));
        $this->assertNotNull($joiner->getCommandMessage(CommandMessage::createNotifyOrderRewardsSentCommand($slaveGroupUserOrder)));

        //6. 团员订单发货，团员收货
        $slaveGroupUserOrder->setDelivered();
        $this->assertEquals($captain->getPendingTotalRewards(), $masterGroupUserOrder->getOrderRewards() + $slaveGroupUserOrder->getGroupUserOrderRewards()[0]->getUserRewards());
        $this->assertEquals(0, $captain->getTotalRewards());

        $this->assertEquals($joiner->getPendingTotalRewards(), $slaveGroupUserOrder->getOrderRewards());
        $this->assertEquals(0, $joiner->getTotalRewards());

        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createNotifyUserRewardsSentCommand($slaveGroupUserOrder)));
        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createSendUserRewardsCommand($slaveGroupUserOrder)));

    }


    /**
     * 自然流量进来的开团成功后过期
     * 1. 团长开团，支付，分享
     * 2. 团员参团
     * 3. 拼团过期
     */
    public function testExpiredGroupOrder() {

        $product = $this->createProduct();
        $oldStock = $product->getStock();
        $captain = $this->createUser();

        //1. 创建拼团订单及团长客户订单
        $groupOrder = new GroupOrder($captain, $product);
        $groupOrder->setId(rand());
        $masterGroupUserOrder = $groupOrder->getMasterGroupUserOrder();

        //2. 团长支付成功
        $groupOrder->setPending();

        //3. 拼团过期
        $groupOrder->setExpired();

        $this->assertTrue($masterGroupUserOrder->isCancelled());
        $this->assertTrue($masterGroupUserOrder->isRefunding());
        $this->assertTrue($groupOrder->isExpired());
        $this->assertEquals($oldStock, $product->getStock());

        $captainStatistics = $captain->getUserStatistics()[0];
        $this->assertEquals(0, $captainStatistics->getChildrenNum());
        $this->assertEquals(0, $captainStatistics->getSharedNum());
        $this->assertEquals(1, $captainStatistics->getGroupOrderNum());
        $this->assertEquals(0, $captainStatistics->getGroupOrderJoinedNum());
        $this->assertEquals(1, $captainStatistics->getGroupUserOrderNum());
        $this->assertEquals(0, $captainStatistics->getSpentTotal());
        $this->assertEquals(0, $captainStatistics->getOrderRewardsTotal());
        $this->assertEquals(0, $captainStatistics->getUserRewardsTotal());

        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createNotifyExpiredGroupOrderCommand($groupOrder)));
        $this->assertNotNull($captain->getCommandMessage(CommandMessage::createRefundOrderCommand($masterGroupUserOrder)));

    }
}