<?php

namespace App\Repository;

use App\Entity\ProductVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductVideo[]    findAll()
 * @method ProductVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVideoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductVideo::class);
    }
}
