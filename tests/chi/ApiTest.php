<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-02
 * Time: 11:47 PM
 */

namespace App\Tests\Chi;


use App\Entity\GroupOrder;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ShareSourceRepository;
use App\Repository\ShareSourceUserRepository;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\BaseTestCase;

/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-02
 * Time: 11:02 PM
 */

class ApiTest extends BaseTestCase
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var User
     */
    private $rootUser; //根用户

    /**
     * @var User
     */
    private $captain; //团长

    /**
     * @var User
     */
    private $joiner; //团员

    /**
     * @var User
     */
    private $visitor; //游客

    protected function setUp() {
        parent::setUp();
        $this->product = $this->createProduct(true);
        $this->rootUser = $this->createUser(true);
        $this->captain = $this->createUser(true);
        $this->joiner = $this->createUser(true);
        $this->visitor = $this->createUser(true);
    }

    public function testProductApi() {

        $client = static::createClient();
        $client->request('GET', '/wxapi/products/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->product->getArray(), $data['data']['products'][0]);

        $testRedirectUrl = 'testRedirectUrl';
        $client->request('GET', '/wxapi/products/' . $this->product->getId(), ['url' => $testRedirectUrl]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->product->getArray(), $data['data']['product']);
        $this->assertEquals(ShareSource::REFER, $data['data']['shareSources'][ShareSource::REFER]['type']);
        $this->assertEquals(ShareSource::QUAN, $data['data']['shareSources'][ShareSource::QUAN]['type']);

    }

    /**
     * 通过API接口模拟团长开团，团员参团流程
     */
    public function testGroupOrderApi() {
        /**
         * @var GroupOrderRepository $groupOrderRepository
         */
        $groupOrderRepository = $this->getEntityManager()->getRepository(GroupOrder::class);

        /**
         * @var GroupUserOrderRepository $groupUserOrderRepository
         */
        $groupUserOrderRepository = $this->getEntityManager()->getRepository(GroupUserOrder::class);


        /**
         * @var ShareSourceRepository $shareSourceRepository
         */
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ShareSourceUserRepository $shareSourceUserRepository
         */
        $shareSourceUserRepository = $this->getEntityManager()->getRepository(ShareSourceUser::class);

        //1. 团长开团
        $captainClient = static::createClient();
        $captainClient->request('POST', '/wxapi/groupOrder/create', [], [], [], json_encode([
            'thirdSession' => $this->captain->getId(),
            'productId' => $this->product->getId()
        ]));

        $this->assertEquals(200, $captainClient->getResponse()->getStatusCode());
        $data = json_decode($captainClient->getResponse()->getContent(), true);

        /**
         * @var GroupUserOrder $masterGroupUserOrder
         */
        $masterGroupUserOrder = $groupUserOrderRepository->findAll()[0];
        $this->assertEquals($masterGroupUserOrder->getArray(), $data['data']['groupUserOrder']);

        //2. 团长支付成功
        $captainClient->request('POST', '/wxapi/groupUserOrder/notifyPayment', [], [], [], json_encode(
            [
                'thirdSession' => $this->captain->getId(),
                'isPaid' => true,
                'groupUserOrderId' => $masterGroupUserOrder->getId(),
            ]
        ));

        $this->assertEquals(200, $captainClient->getResponse()->getStatusCode());
        $data = json_decode($captainClient->getResponse()->getContent(), true);

        //刷新本地数据库数据
        $this->getEntityManager()->refresh($masterGroupUserOrder);
        $this->getEntityManager()->refresh($masterGroupUserOrder->getProduct());
        $this->getEntityManager()->refresh($masterGroupUserOrder->getGroupOrder());

        $this->assertEquals($masterGroupUserOrder->getArray(), $data['data']['groupUserOrder']);
        $this->assertTrue($masterGroupUserOrder->getGroupOrder()->isPending());

        //3. 团长分享
        $testRedirectUrl = 'testRedirectUrl';
        $captainClient->request('POST', '/wxapi/groupOrder/view', [], [], [], json_encode([
            'groupOrderId' => $masterGroupUserOrder->getGroupOrder()->getId(),
            'url' => $testRedirectUrl
        ]));
        $this->assertEquals(200, $captainClient->getResponse()->getStatusCode());
        $data = json_decode($captainClient->getResponse()->getContent(), true);
        $shareSourceId = $data['data']['shareSources'][0]['id'];
        $this->assertEquals(ShareSource::REFER, $data['data']['shareSources'][0]['type']);
        $this->assertEquals(ShareSource::QUAN, $data['data']['shareSources'][1]['type']);

        $captainClient->request('POST', '/wxapi/user/shareSource/create', [], [], [], json_encode([
            'thirdSession' => $this->captain->getId(),
            'shareSourceId' => $shareSourceId,
            'productId' => $this->product->getId(),
            'shareSourceType' => ShareSource::REFER,
            'url' => $testRedirectUrl,
            'groupOrderId' => $masterGroupUserOrder->getGroupOrder()->getId(),
            'bannerFileId' => $this->createFile(true)->getId(),
            'title' => 'test title'
        ]));

        $this->assertEquals(200, $captainClient->getResponse()->getStatusCode());
        $data = json_decode($captainClient->getResponse()->getContent(), true);
        $shareSource = $shareSourceRepository->findAll()[0];
        $this->assertEquals($shareSource->getArray(), $data['data']['shareSource']);

        //4. 团员通过分享链接进入拼团页面
        $joinerClient = self::createClient();
        $joinerClient->request('POST', '/wxapi/user/shareSource/addUser', [],[],[], json_encode([
            'thirdSession' => $this->joiner->getId(),
            'shareSourceId' => $shareSourceId,
        ]));
        $this->assertEquals(200, $joinerClient->getResponse()->getStatusCode());
        $data = json_decode($joinerClient->getResponse()->getContent(), true);
        $shareSourceUser = $shareSourceUserRepository->findOneBy(['shareSource' => $shareSourceId]);
        $this->assertEquals($shareSourceUser->getArray(), $data['data']['shareSourceUser']);

        //5. 团员创建参团
        $joinerClient->request('POST', '/wxapi/groupOrder/join', [],[], [], json_encode([
            'thirdSession' => $this->joiner->getId(),
            'groupOrderId' => $masterGroupUserOrder->getGroupOrder()->getId(),
        ]));
        $this->assertEquals(200, $joinerClient->getResponse()->getStatusCode());
        $data = json_decode($joinerClient->getResponse()->getContent(), true);
        $slaveGroupUserOrder = $groupUserOrderRepository->findOneBy(['user' => $this->joiner->getId()]);
        $this->assertEquals($slaveGroupUserOrder->getArray(), $data['data']['groupUserOrder']);

        //6. 团员参团，支付成功
        $joinerClient->request('POST', '/wxapi/groupUserOrder/notifyPayment', [], [], [], json_encode(
            [
                'thirdSession' => $this->joiner->getId(),
                'isPaid' => true,
                'groupUserOrderId' => $slaveGroupUserOrder->getId(),
            ]
        ));
        $this->assertEquals(200, $joinerClient->getResponse()->getStatusCode());
        $data = json_decode($joinerClient->getResponse()->getContent(), true);

        //刷新本地数据库数据
        $this->getEntityManager()->refresh($slaveGroupUserOrder);
        $this->getEntityManager()->refresh($slaveGroupUserOrder->getProduct());
        $this->getEntityManager()->refresh($slaveGroupUserOrder->getGroupOrder());
        $this->getEntityManager()->refresh($slaveGroupUserOrder->getUser());

        $this->assertTrue($slaveGroupUserOrder->getGroupOrder()->isCompleted());
        $this->assertEquals($slaveGroupUserOrder->getArray(), $data['data']['groupUserOrder']);

        //7. 团员成为团长有效下线
        $slaveGroupUserOrder->setDelivered();
        $this->getEntityManager()->persist($slaveGroupUserOrder);
        $this->getEntityManager()->flush();

        $captainClient->request('POST', '/wxapi/user/rewards/list', [],[],[], json_encode([
            'thirdSession' => $this->captain->getId(),
        ]));
        $this->assertEquals(200, $joinerClient->getResponse()->getStatusCode());
        $data = json_decode($captainClient->getResponse()->getContent(), true);

        $this->assertEquals($this->joiner->getId(), $data['data']['children'][0]['id']);

    }

}