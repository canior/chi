<?php

namespace App\Repository;

use App\Entity\UserUpgradeCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserUpgradeCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserUpgradeCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserUpgradeCode[]    findAll()
 * @method UserUpgradeCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserUpgradeCodeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserUpgradeCode::class);
    }
}
