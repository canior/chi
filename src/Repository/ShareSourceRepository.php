<?php

namespace App\Repository;

use App\Entity\ShareSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShareSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareSource[]    findAll()
 * @method ShareSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareSourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShareSource::class);
    }

//    /**
//     * @return ShareSource[] Returns an array of ShareSource objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShareSource
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
