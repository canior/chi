<?php

namespace App\Repository;

use App\Entity\GroupUserOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupUserOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupUserOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupUserOrder[]    findAll()
 * @method GroupUserOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupUserOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupUserOrder::class);
    }

    /**
     * @param null $groupOrderId
     * @param null $groupUserOrderId
     * @param null $userId
     * @param null $productName
     * @param null $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findGroupUserOrdersQueryBuilder($groupOrderId = null, $groupUserOrderId = null, $userId = null, $productName = null, $status = null)
    {
        $query = $this->createQueryBuilder('guo');

        if ($groupOrderId || $productName) {
            $query->leftJoin('guo.groupOrder', 'go');
            if ($groupOrderId) {
                $query->where('go.id = :groupOrderId')
                    ->setParameter('groupOrderId', $groupOrderId);
            }

            if ($productName) {
                $literal = $query->expr()->literal("%$productName%");
                $query->leftJoin('go.product', 'p')
                    ->andWhere($query->expr()->like('p.title', $literal));
            }
        }

        if ($groupUserOrderId) {
            $query->andWhere('guo.id = :groupUserOrderId')
                ->setParameter('groupUserOrderId', $groupUserOrderId);
        }

        if ($userId) {
            $query->andWhere('guo.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($status) {
            $query->andWhere('guo.status = :status')
                ->setParameter('status', $status);
        }

        return $query->orderBy('guo.id', 'DESC');
    }
}
