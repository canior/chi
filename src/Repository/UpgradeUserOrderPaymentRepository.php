<?php

namespace App\Repository;

use App\Entity\UpgradeUserOrderPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UpgradeUserOrderPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpgradeUserOrderPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpgradeUserOrderPayment[]    findAll()
 * @method UpgradeUserOrderPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpgradeUserOrderPaymentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UpgradeUserOrderPayment::class);
    }

}
