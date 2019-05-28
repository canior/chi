<?php

namespace App\Repository;

use App\Entity\MessageCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MessageCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageCode[]    findAll()
 * @method MessageCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageCodeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MessageCode::class);
    }

    // /**
    //  * @return MessageCode[] Returns an array of MessageCode objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MessageCode
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
