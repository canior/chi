<?php

namespace App\Repository;

use App\Entity\ProductSimilar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductSimilar|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductSimilar|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductSimilar[]    findAll()
 * @method ProductSimilar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductSimilarRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductSimilar::class);
    }

//    /**
//     * @return ProductSimilar[] Returns an array of ProductSimilar objects
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
    public function findOneBySomeField($value): ?ProductSimilar
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
