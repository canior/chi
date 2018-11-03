<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-03
 * Time: 12:28 AM
 */

namespace App\Tests;


use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;

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

        //创建拼团订单及团长客户订单
        $groupOrder = new GroupOrder($captain, $product);
        $this->assertTrue($groupOrder->isCreated());

        $masterGroupUserOrder = $groupOrder->getMasterGroupUserOrder();
        $this->assertTrue($masterGroupUserOrder->isCreated());
        $this->assertFalse($masterGroupUserOrder->isPaid());
        $this->assertTrue($masterGroupUserOrder->isGroupOrder());
        $this->assertTrue($masterGroupUserOrder->isMasterOrder());
        $this->assertEquals($product->getGroupPrice(), $masterGroupUserOrder->getTotal());

        //团长支付成功
        $groupOrder->setPending();

        $this->assertTrue($groupOrder->isPending());
        $this->assertTrue($masterGroupUserOrder->isPaid());
        $this->assertEquals($oldStock - 2, $product->getStock());

        $captainStatistics = $captain->getUserStatistics()[0];
        $this->assertEquals($captainStatistics->getGroupOrderNum(), 1);

        //团长分享给团员
        $captainShareSource = $this->createShareSource($groupOrder, $product, $captain);

        //团员打开分享链接
        $joiner = $this->createUser();
        $shareSourceUser = new ShareSourceUser($captainShareSource, $joiner);
        $captainShareSource->addShareSourceUser($shareSourceUser);

        $this->assertEquals(1, $captainStatistics->getSharedNum());
        $this->assertNull($joiner->getParentUser()); //此时团员的有效上线为空

        //团员创建拼团订单
        $slaveGroupUserOrder = new GroupUserOrder($joiner, $product);
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

        //团员支付拼团订单
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

        $joinerStatistics = $joiner->getUserStatistics()[0];
        $this->assertEquals(0, $joinerStatistics->getChildrenNum());
        $this->assertEquals(1, $joinerStatistics->getGroupOrderJoinedNum());
        $this->assertEquals(1, $joinerStatistics->getGroupUserOrderNum());

        //团员订单发货，团员收货
        $slaveGroupUserOrder->setDelivered();
        //$this->assertEquals($product->getParentRewards(), $captain->getTotalRewards());

    }
}