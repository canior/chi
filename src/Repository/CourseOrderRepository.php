<?php

namespace App\Repository;

use App\Entity\GroupGiftOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CourseOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseOrder[]    findAll()
 * @method CourseOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CourseOrder::class);
    }
}
