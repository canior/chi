<?php

namespace App\Repository;

use App\Entity\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Teacher|null find($id, $lockMode = null, $lockVersion = null)
 * @method Teacher|null findOneBy(array $criteria, array $orderBy = null)
 * @method Teacher[]    findAll()
 * @method Teacher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Teacher::class);
    }

    /**
     * @param $teacherId
     * @param $courseId
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTotalStudents($teacherId, $courseId = null) {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('count(distinct cs.studentUser)')
            ->from('App:CourseStudent', 'cs')
            ->leftJoin('cs.course', 'c')
            ->where('c.teacher = :teacherId')
            ->setParameter('teacherId', $teacherId);

        if ($courseId) {
                $query->andWhere('c.id = :courseId')
                ->setParameter('courseId', $courseId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

}
