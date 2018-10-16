<?php

namespace App\Repository;

use App\Entity\GroupOrderRewards;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupOrderRewards|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupOrderRewards|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupOrderRewards[]    findAll()
 * @method GroupOrderRewards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupOrderRewardsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupOrderRewards::class);
    }

//    /**
//     * @return GroupOrderRewards[] Returns an array of GroupOrderRewards objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GroupOrderRewards
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
