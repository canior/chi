<?php

namespace App\Repository;

use App\Entity\UpgradeOrderCoupon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @method UpgradeOrderCoupon|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpgradeOrderCoupon|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpgradeOrderCoupon[]    findAll()
 * @method UpgradeOrderCoupon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpgradeOrderCouponRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UpgradeOrderCoupon::class);
    }

    /**
     * @param $num
     * @return array
     */
    public function createCoupons($num) {
        $coupons = [];

        for($i = 0; $i < $num; $i++) {
            $coupon = null;

            while (true) {
                $coupon = $this->createRandomLetters(6);
                if ($this->findBy(['coupon' => $coupon]) == null
                and !in_array($coupon, $coupons))
                    break;
            }

            $coupons[] = $coupon;
        }

        return $coupons;
    }

    /**
     * @param $length
     * @return string
     */
    private function createRandomLetters($length) {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= chr(rand(ord('A'), ord('Z')));
        }
        return $random;
    }
}
