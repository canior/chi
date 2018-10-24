<?php

namespace App\Repository;

use App\Entity\ProductStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductStatistics[]    findAll()
 * @method ProductStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductStatistics::class);
    }

//    /**
//     * @return ProductStatistics[] Returns an array of ProductStatistics objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductStatistics
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
