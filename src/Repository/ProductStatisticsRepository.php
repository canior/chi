<?php

namespace App\Repository;

use App\Entity\ProductStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductStatistics[]    findAll()
 * @method ProductStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductStatistics::class);
    }

    /**
     * @param null $productId
     * @param null $year
     * @param null $month
     * @param null $day
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductStatisticsQueryBuilder($productId = null, $year = null, $month = null, $day = null)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('ps AS productStatistics')
            ->addSelect('SUM(ps.reviewsNum) AS reviewsNum')
            ->addSelect('SUM(ps.orderAmountTotal) AS orderAmountTotal')
            ->addSelect('SUM(ps.orderNum) AS orderNum')
            ->addSelect('SUM(ps.buyersNum) AS buyersNum')
            ->addSelect('SUM(ps.returnUsersNum) AS returnUsersNum')
            ->addSelect('SUM(ps.returnUsersRate) AS returnUsersRate')
            ->from('App:ProductStatistics', 'ps')
            ->groupBy('ps.product')
            ->addOrderBy('orderNum', 'DESC')
            ->addOrderBy('buyersNum', 'DESC')
            ->addOrderBy('returnUsersNum', 'DESC')
            ->addOrderBy('returnUsersRate', 'DESC');

        if ($productId) {
            $query->where('ps.product = :productId')
                ->setParameter('productId', $productId);
        }

        if ($year) {
            $query->andWhere('ps.year = :year')
                ->setParameter('year', $year);
        }

        if ($month) {
            $query->andWhere('ps.month = :month')
                ->setParameter('month', $month);
        }

        if ($day) {
            $query->andWhere('ps.day = :day')
                ->setParameter('day', $day);
        }

        return $query;
    }
}
