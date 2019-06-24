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
}
