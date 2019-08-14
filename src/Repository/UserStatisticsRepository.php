<?php

namespace App\Repository;

use App\Entity\UserStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStatistics[]    findAll()
 * @method UserStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserStatistics::class);
    }

    /**
     * @param null $userId
     * @param null $username
     * @param null $year
     * @param null $month
     * @param null $day
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUserStatisticsQueryBuilder($userId = null, $username = null, $year = null, $month = null, $day = null)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('us AS userStatistics')
            ->addSelect('SUM(us.childrenNum) AS childrenNum')
            ->addSelect('SUM(us.sharedNum) AS sharedNum')
            ->addSelect('SUM(us.groupOrderNum) AS groupOrderNum')
            ->addSelect('SUM(us.groupOrderJoinedNum) AS groupOrderJoinedNum')
            ->addSelect('SUM(us.groupUserOrderNum) AS groupUserOrderNum')
            ->addSelect('SUM(us.spentTotal) AS spentTotal')
            ->addSelect('SUM(us.orderRewardsTotal) AS orderRewardsTotal')
            ->addSelect('SUM(us.userRewardsTotal) AS userRewardsTotal')
            ->from('App:UserStatistics', 'us')
            ->groupBy('us.user')
            // order by 拼团消费总额
            ->addOrderBy('spentTotal', 'DESC')
            // order by 总分享数量
            ->addOrderBy('sharedNum', 'DESC')
            // order by 有效下线用户数量
            ->addOrderBy('childrenNum', 'DESC');

        if ($userId) {
            $query->where('us.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($username) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$username%");
            $orX->add($query->expr()->like('u.username', $literal));
            $orX->add($query->expr()->like('u.nickname', $literal));
            $query->leftJoin('us.user', 'u')
                ->andWhere($orX);
        }

        if ($year) {
            $query->andWhere('us.year = :year')
                ->setParameter('year', $year);
        }

        if ($month) {
            $query->andWhere('us.month = :month')
                ->setParameter('month', $month);
        }

        if ($day) {
            $query->andWhere('us.day = :day')
                ->setParameter('day', $day);
        }

        $query->orderBy('us.id', 'desc');

        return $query;
    }
}
