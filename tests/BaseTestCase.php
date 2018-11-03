<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-03
 * Time: 12:05 AM
 */

namespace App\Tests;


use App\Entity\File;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ShareSource;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    private $entityManager;

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getEntityManager() {
        return $this->entityManager;
    }

    protected function setUp() {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /**
         * @var UserRepository $userRepository
         */
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        foreach ($userRepository->findAll() as $user) {
            $user->setParentUser(null);
            $this->getEntityManager()->persist($user);
        }
        $this->getEntityManager()->flush();

        foreach ($userRepository->findAll() as $user) {
            $this->getEntityManager()->remove($user);
        }
        $this->getEntityManager()->flush();


        /**
         * @var ProductRepository
         */
        $productRepository = $this->getEntityManager()->getRepository(Product::class);
        foreach ($productRepository->findAll() as $product) {
            $this->getEntityManager()->remove($product);
        }
        $this->getEntityManager()->flush();

    }

    public function createId() {
        return hexdec(uniqid());
    }

    /**
     * @param bool $isPersist
     * @return Product
     */
    public function createProduct($isPersist = false) {
        $product = new Product();
        if (!$isPersist)
            $product->setId($this->createId());
        $product->setTitle('test product title');
        $product->setShortDescription('test product short description');
        $product->setFreight(0);
        $product->setPrice(100);
        $product->setGroupPrice(80);
        $product->setOriginalPrice(120);
        $product->setRewards(20);
        $product->setSku('TEST');
        $product->setStock(100);
        $product->setCaptainRewards(5);
        $product->setJoinerRewards(5);
        $product->setRegularRewards(2);
        $product->setParentRewards(1);

        $productImage = new ProductImage();
        $productImage->setProduct($product);
        $productImage->setFile($this->createFile($isPersist));
        $product->addProductImage($productImage);

        if ($isPersist) {
            $this->getEntityManager()->persist($product);
            $this->getEntityManager()->flush();
        }
        return $product;
    }

    public function createUser($isPersist = false) {
        $openId = uniqid();
        $user = new User();
        if (!$isPersist)
            $user->setId($this->createId());
        $user->setUsername($openId);
        $user->setUsernameCanonical($openId);
        $user->setEmail($openId . '@qq.com');
        $user->setEmailCanonical($openId . '@qq.com');
        $user->setPassword("IamCustomer");
        $user->setWxOpenId($openId);
        if ($isPersist) {
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }

        return $user;
    }

    public function createShareSource($groupOrder, $product, $user, $isPersist = false) {
        $bannerFile = $this->createFile();
        $shareSource = new ShareSource();
        $shareSource->setUser($user);
        $shareSource->setProduct($product);
        $shareSource->setGroupOrder($groupOrder);
        $shareSource->setPage('share source page');
        $shareSource->setTitle('share source title');
        $shareSource->setType(ShareSource::REFER);
        $shareSource->setBannerFile($bannerFile);
        if ($isPersist) {
            $this->getEntityManager()->persist($shareSource);
            $this->getEntityManager()->flush();
        }
        return $shareSource;
    }

    public function createFile($isPersist = false) {
        $file = new File();
        if (!$isPersist)
            $file->setId($this->createId());
        $file->setName('file name');
        $file->setType('image');
        $file->setSize('file size');
        $file->setPath('file path');
        $file->setMd5('file md5');
        if ($isPersist) {
            $this->getEntityManager()->persist($file);
            $this->getEntityManager()->flush();
        }
        return $file;
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}