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
    public function findSpecTradingCourse($price = 12000)
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

        $query->orderBy('c.id', 'desc');
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
        $createdAtStart = $where['createdAtStart'] ?? null;
        $createdAtEnd = $where['createdAtEnd'] ?? null;

        if (!empty($subject)) {
            $query->andWhere('c.subject =:subject')
                ->setParameter('subject', $subject);
        }

        if ( isset($where['subjectText']) && $where['subjectText'] ) {
            $literal = $query->expr()->literal("%".$where['subjectText']."%");
            $query->leftJoin('c.product', 'p')
                ->andWhere($query->expr()->like('p.title', $literal));
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

        if ( isset($where['teacherName']) && $where['teacherName'] ) {
            $literal = $query->expr()->literal("%".$where['teacherName']."%");
            $query->leftJoin('c.teacher', 't')
                ->andWhere($query->expr()->like('t.name', $literal));
        }

        if (!empty($address)) {
            $query->andWhere('c.address =:address')
                ->setParameter('address', $address);
        }

        if ($createdAtStart) {
            if (is_string($createdAtStart)) {
                $createdAtStart = strtotime($createdAtStart);
            }
            $query->andWhere('c.startDate >= :createdAtStart')
                ->setParameter('createdAtStart', $createdAtStart);
        }

        if ($createdAtEnd) {
            if (is_string($createdAtEnd)) {
                $createdAtEnd = strtotime($createdAtEnd);
            }
            $query->andWhere('c.startDate <= :createdAtEnd')
                ->setParameter('createdAtEnd', $createdAtEnd);
        }

        $query->orderBy('c.id', 'desc');
        return $query;
    }


    /**
     * 查询课程列表
     */
    public function courseQuery($where = [],$count = false )
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.product', 'p');

        if( isset($where['isOnline']) && $where['isOnline'] ){
            $query->andWhere('c.isOnline =:isOnline')->setParameter('isOnline', $where['isOnline']);
        }

        if( isset($where['title']) && $where['title'] ){
            $query->andWhere('p.title like :title')->setParameter('title', '%'.$where['title'].'%');
        }

        if( isset($where['category_id']) && $where['category_id'] ){
            die;
            $query->andWhere('c.courseCategory = :category_id')->setParameter('category_id', $where['category_id']);
        }

        if( isset($where['status']) && $where['status'] ){
            $query->andWhere('p.status = :status')->setParameter('status', $where['status']);
        }

        if( isset($where['show_type']) && $where['show_type'] ){
            $query->andWhere('c.courseShowType = :show_type')->setParameter('show_type', $where['show_type']);
        }

        if( isset($where['teacher_id']) && $where['teacher_id'] ){
            $query->andWhere('c.teacher = :teacher')->setParameter('teacher', $where['teacher_id']);
        }

        if( isset($where['update_at']) && $where['update_at'] ){
            $query->andWhere('p.updatedAt = :update_at')->setParameter('update_at', $where['update_at']);
        }

        // 数量
        if( $count ){
            $query->select('count(c.id)');
            return $query;
        }


        $sort = 'c.id';
        if( isset($where['sortkey']) && $where['sortkey'] ){
            $sort = $where['sortkey'];
        }

        $order = 'desc';
        if( isset($where['orderkey']) && $where['orderkey'] ){
            $order = $where['orderkey'];
        }

        $query->orderBy($sort, $order);

      
        return $query;
    }
}
