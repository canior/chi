<?php

namespace App\Repository;

use App\Entity\GroupGiftOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupGiftOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupGiftOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupGiftOrder[]    findAll()
 * @method GroupGiftOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupGiftOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupGiftOrder::class);
    }
}
