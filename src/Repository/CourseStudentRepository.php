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


    /**
     * @param bool $isCourse
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function courseStudentsQueryBuilder($courseId = null,$isGetCount = false)
    {
        $query = $this->createQueryBuilder('cs')
            ->orderBy('cs.id', 'DESC');

        if ($courseId) {
            $query->where('cs.course = :courseId')
            ->setParameter('courseId', $courseId);
        }

        if ($isGetCount) {
            $query->select('count(cs.id)');
            return $query;
        }

        return $query;
    }
}
