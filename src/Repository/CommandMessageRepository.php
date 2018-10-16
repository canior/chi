<?php

namespace App\Repository;

use App\Entity\CommandMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CommandMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandMessage[]    findAll()
 * @method CommandMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommandMessage::class);
    }

//    /**
//     * @return CommandMessage[] Returns an array of CommandMessage objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommandMessage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
