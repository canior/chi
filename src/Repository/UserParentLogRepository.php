<?php

namespace App\Repository;

use App\Entity\UserParentLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserParentLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserParentLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserParentLog[]    findAll()
 * @method UserParentLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserParentLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserParentLog::class);
    }

}
