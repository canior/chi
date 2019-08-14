<?php

namespace App\Repository;

use App\DataAccess\DataAccess;
use App\Entity\Course;
use App\Entity\Product;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param bool $isCourse
     * @param bool $isOnlineCourse
     * @param null $courseShowType
     * @return \Doctrine\ORM\Query
     */
    public function findActiveProductsQuery($isCourse = false, $isOnlineCourse = true, $courseShowType = null)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::ACTIVE);
        if ($isCourse) {
            $query->join('p.course', 'c')
                ->andWhere('c.isOnline = :isOnline')
                ->setParameter('isOnline', $isOnlineCourse);


            //课程显示设备处理
            if ($isOnlineCourse) {
                if (!is_null($courseShowType)) {
                    $query->andWhere('c.courseShowType in (:courseShowType)')
                        ->setParameter('courseShowType', array_unique([$courseShowType, Course::COURSE_SHOW_TYPE_ALL]));
                }
            }
        } else {
            $query->andWhere('p.course is null');
        }
        return $query->orderBy('p.priority', 'DESC')->addOrderBy('p.id', 'DESC')->getQuery();
    }

    /**
     * @param bool $isCourse
     * @param null $keyword
     * @param null $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductsQueryBuilder($isCourse = false, $keyword = null, $status = null)
    {
        $query = $this->createQueryBuilder('p');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('p.title', $literal));
            $orX->add($query->expr()->like('p.shortDescription', $literal));
            $query->andWhere($orX);
        }

        if ($isCourse) {
            $query->andWhere('p.course is not null');
        } else {
            $query->andWhere('p.course is null');
        }

        if ($status) {
            $query->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $query->orderBy('p.priority', 'DESC')->addOrderBy('p.id', 'DESC');
    }

    /**
     * @param bool $isCourse
     * @param null $isOnline
     * @param array $extension
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findAppProductsQueryBuilder($isCourse = false, $isOnline = null, $extension = [])
    {
        $query = $this->createQueryBuilder('p')
        ->where('p.status = :status')
        ->setParameter('status', Product::ACTIVE);

        $orderBy = $extension['orderBy'] ?? [];
        $limit = $extension['limit'] ?? null;
        $offlineCourseType = $extension['offlineCourseType'] ?? null;
        $initiator = $extension['initiator'] ?? null;
        $courseShowType = $extension['courseShowType'] ?? Course::COURSE_SHOW_TYPE_APP;
        $isGetCount = $extension['isGetCount'] ?? false;

        if ($isCourse) {
            if(is_null($isOnline)) {
                $query->andWhere('p.course is not null');
            } else {
                $query->join('p.course', 'c')
                    ->andWhere('c.isOnline = :isOnline')
                    ->setParameter('isOnline', $isOnline);

                //线下课程类型
                if (!is_null($offlineCourseType)) {
                    $subjects = [];
                    if ($offlineCourseType == 'THINKING') {
                        $subjects[] = Subject::THINKING;
                    } else if ($offlineCourseType == 'SYSTEM') {
                        $subjects[] = Subject::TRADING;
                        $subjects[] = Subject::SYSTEM_1;
                        $subjects[] = Subject::SYSTEM_2;
                        $subjects[] = Subject::SYSTEM_3;
                    } else if($offlineCourseType == 'PRIVATE_DIRECTOR') {
                        $subjects[] = Subject::PRIVATE_DIRECTOR;
                    }

                    if (!empty($subjects)) {
                        $query->andWhere('c.subject  in (:subjects)')
                            ->setParameter('subjects', $subjects);
                    }

                    if (!empty($initiator)) {
                        $query->andWhere('c.initiator = :initiator')
                            ->setParameter('initiator', $initiator);
                    }

                    
                }

                if ($isOnline) {
                    //课程显示设备处理
                    if (!is_null($courseShowType)) {
                        $query->andWhere('c.courseShowType  in (:courseShowType)')
                            ->setParameter('courseShowType', array_unique([$courseShowType, Course::COURSE_SHOW_TYPE_ALL]));
                    }
                }
            }

            if( isset($extension['isEnd']) ){
                if( $extension['isEnd'] == true ){
                    $query->andWhere('c.endDate < :endDate')->setParameter('endDate', time());
                }else{
                    $query->andWhere('c.endDate >= :endDate')->setParameter('endDate', time());
                }
            }  

        } else {
            $query->andWhere('p.course is null');
        }


        if ($isGetCount) {
            $query->select('count(p.id)');
            return $query;
        }
        if (empty($orderBy)) {
            $orderBy['p.priority'] = 'DESC';
        }

        $orderBy['p.id'] = 'DESC';
        $flag = false;
        foreach ($orderBy as $oKey => $oVal) {
            $orderMethod = $flag ? 'addOrderBy' : 'orderBy';
            call_user_func_array([$query, $orderMethod], [$oKey, $oVal]);
            $flag = true;
        }

        if (!empty($limit)) {
            $query->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * 获取首页最新课程列表
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findHomeNewestCourses()
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::ACTIVE);
        $query->join('p.course', 'c')
            ->andWhere('c.isOnline = :isOnline')
            ->setParameter('isOnline', true);

        $query->andWhere('c.courseShowType  in (:courseShowType)')
            ->setParameter('courseShowType', array_unique([Course::COURSE_SHOW_TYPE_APP, Course::COURSE_SHOW_TYPE_ALL]));

        $query->andWhere('c.isShowNewest =:isShowNewest')
            ->setParameter('isShowNewest', true);
        $query->addOrderBy('p.priority', 'DESC')->addOrderBy('p.id', 'DESC');

        return $query;
    }
}
