<?php

namespace App\Repository;

use App\Entity\UserStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStatistics[]    findAll()
 * @method UserStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserStatistics::class);
    }

//    /**
//     * @return UserStatistics[] Returns an array of UserStatistics objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserStatistics
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
