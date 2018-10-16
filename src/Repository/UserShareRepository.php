<?php

namespace App\Repository;

use App\Entity\UserShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserShare[]    findAll()
 * @method UserShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserShareRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserShare::class);
    }

//    /**
//     * @return UserShare[] Returns an array of UserShare objects
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
    public function findOneBySomeField($value): ?UserShare
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
