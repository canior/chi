<?php

namespace App\Repository;

use App\Entity\GroupUserOrderRewards;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupUserOrderRewards|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupUserOrderRewards|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupUserOrderRewards[]    findAll()
 * @method GroupUserOrderRewards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupUserOrderRewardsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupUserOrderRewards::class);
    }

//    /**
//     * @return GroupUserOrderRewards[] Returns an array of GroupUserOrderRewards objects
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
    public function findOneBySomeField($value): ?GroupUserOrderRewards
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
