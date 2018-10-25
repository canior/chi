<?php

namespace App\Repository;

use App\Entity\ShareSourceUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShareSourceUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareSourceUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareSourceUser[]    findAll()
 * @method ShareSourceUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareSourceUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShareSourceUser::class);
    }
}
