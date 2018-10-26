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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductStatisticsQueryBuilder($productId = null)
    {
        $query = $this->createQueryBuilder('ps')
            ->addOrderBy('ps.orderNum', 'DESC')
            ->addOrderBy('ps.buyersNum', 'DESC')
            ->addOrderBy('ps.returnUsersNum', 'DESC')
            ->addOrderBy('ps.returnUsersRate', 'DESC');

        if ($productId) {
            $query->where('ps.product = :productId')
                ->setParameter('productId', $productId);
        }

        return $query;
    }
}
