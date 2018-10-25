<?php

namespace App\Repository;

use App\Entity\GroupOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupOrder[]    findAll()
 * @method GroupOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupOrder::class);
    }

    /**
     * @param null $id
     * @param null $groupUserOrderId
     * @param null $userId
     * @param null $productName
     * @param null $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findGroupOrdersQueryBuilder($id = null, $groupUserOrderId = null, $userId = null, $productName = null, $status = null)
    {
        $query = $this->createQueryBuilder('go');

        if ($id) {
            $query->where('go.id = :id')
                ->setParameter('id', $id);
        }

        if ($groupUserOrderId || $userId) {
            $query->leftJoin('go.groupUserOrders', 'guo');
            if ($groupUserOrderId) {
                $query->andWhere('guo.id = :groupUserOrderId')
                    ->setParameter('groupUserOrderId', $groupUserOrderId);
            }

            if ($userId) {
                $query->andWhere('guo.user = :userId')
                    ->setParameter('userId', $userId);
            }
        }

        if ($productName) {
            $literal = $query->expr()->literal("%$productName%");
            $query->leftJoin('go.product', 'p')
                ->andWhere($query->expr()->like('p.title', $literal));
        }

        if ($status) {
            $query->andWhere('go.status = :status')
                ->setParameter('status', $status);
        }

        return $query->orderBy('go.id', 'DESC');
    }
}
