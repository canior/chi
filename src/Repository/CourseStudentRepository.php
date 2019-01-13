<?php

namespace App\Repository;

use App\Entity\CourseStudent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CourseStudent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseStudent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseStudent[]    findAll()
 * @method CourseStudent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseStudentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CourseStudent::class);
    }
}
