<?php

namespace App\Repository;

use App\Entity\ProjectTokenMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectTokenMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectTokenMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectTokenMeta[]    findAll()
 * @method ProjectTokenMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectTokenMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectTokenMeta::class);
    }
}
