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

}
