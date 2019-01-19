<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\UserRecommandStockOrder;

/**
 * @method UserRecommandStockOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRecommandStockOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRecommandStockOrder[]    findAll()
 * @method UserRecommandStockOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRecommandStockOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserRecommandStockOrder::class);
    }

}
