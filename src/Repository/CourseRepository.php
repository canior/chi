<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 * @method Course[]    findAll()
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * 查询报名直升直通车直通车课程
     * @author zxqc2018
     * @param int $price 直通车加个
     * @return Course|null
     */
    public function findSpecTradingCourse($price = 0.02)
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.product', 'p')
            ->where('c.subject =:subject')
            ->setParameter('subject', Subject::TRADING)
            ->andWhere('c.isOnline =:online')
            ->setParameter('online', false)
            ->andWhere($query->expr()->eq('p.price', $price));
        $result = $query->getQuery()->getResult();

        return $result[0] ?? null;
    }

    /**
     * 查询课程列表
     * @param bool $online
     * @param null $courseShowType
     * @param null $oneCategory
     * @param null $twoCategory
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findCourseQueryBuild($online = true, $courseShowType = null, $oneCategory = null, $twoCategory = null)
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.isOnline =:online')
            ->setParameter('online', $online);

        if (!is_null($courseShowType) && $courseShowType != Course::COURSE_SHOW_TYPE_ALL) {
            $query->andWhere('c.courseShowType in (:courseShowType)')
                ->setParameter('courseShowType', array_unique([$courseShowType, Course::COURSE_SHOW_TYPE_ALL]));
        }

        //分类查询
        if (!empty($oneCategory) || !empty($twoCategory)) {
            $query->innerJoin('c.courseActualCategory', 'cac');
            if (!empty($oneCategory)) {
                $query->andWhere('cac.parentCategory =:oneCategory')
                    ->setParameter('oneCategory', $oneCategory);
            }

            if (!empty($twoCategory)) {
                $query->andWhere('cac.id =:twoCategory')
                    ->setParameter('twoCategory', $twoCategory);
            }
        }

        return $query;
    }


    /**
     * 查询课程列表
     * @param array $where
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findOfflineCourseQueryBuild($where = [])
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.isOnline =:online')
            ->setParameter('online', false);

        $subject = $where['subject'] ?? null;
        $status = $where['status'] ?? null;
        $teacher = $where['teacher'] ?? null;
        $address = $where['address'] ?? null;

        if (!empty($subject)) {
            $query->andWhere('c.subject =:subject')
                ->setParameter('subject', $subject);
        }

        if (!empty($status)) {
            $query->innerJoin('c.product', 'p');
            $query->andWhere('p.status =:status')
                ->setParameter('status', $status);
        }

        if (!empty($teacher)) {
            $query->andWhere('c.teacher =:teacher')
                ->setParameter('teacher', $teacher);
        }

        if (!empty($address)) {
            $query->andWhere('c.address =:address')
                ->setParameter('address', $address);
        }

        $query->orderBy('c.id', 'desc');
        return $query;
    }
}
