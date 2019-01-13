<?php

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UpgradeUserOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpgradeUserOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpgradeUserOrder[]    findAll()
 * @method UpgradeUserOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpgradeUserOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UpgradeUserOrder::class);
    }

}
