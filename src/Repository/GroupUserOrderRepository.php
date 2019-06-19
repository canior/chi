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
     * @param bool $isCourse
     * @param null $groupOrderId
     * @param null $groupUserOrderId
     * @param null $userId
     * @param null $productName
     * @param null $type
     * @param null $status
     * @param null $paymentStatus
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findGroupUserOrdersQueryBuilder($isCourse = false, $groupOrderId = null, $groupUserOrderId = null, $userId = null, $productName = null, $type = null, $status = null, $paymentStatus = null)
    {
        $query = $this->createQueryBuilder('guo')
            ->orderBy('guo.id', 'DESC');

        if ($groupOrderId) {
            $query->leftJoin('guo.groupOrder', 'go');
            if ($groupOrderId) {
                $query->where('go.id = :groupOrderId')
                    ->setParameter('groupOrderId', $groupOrderId);
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

        if ($type == 'NOT NULL') {
            $query->andWhere('guo.groupOrder IS NOT NULL');
        } elseif ($type == 'NULL') {
            $query->andWhere('guo.groupOrder IS NULL');
        }

        if ($status) {
            $query->andWhere('guo.status = :status')
                ->setParameter('status', $status);
        }

        if ($paymentStatus) {
            $query->andWhere('guo.paymentStatus = :paymentStatus')
                ->setParameter('paymentStatus', $paymentStatus);
        }

        if ($isCourse) {
            $query->leftJoin('guo.product', 'p')
                ->andWhere('p.course is not null');
        } else {
            $query->leftJoin('guo.product', 'p')
                ->andWhere('p.course is null');
        }

        if ($productName) {
            $literal = $query->expr()->literal("%$productName%");
            $query->andWhere($query->expr()->like('p.title', $literal));
        }

        return $query;
    }

    /**
     * @param null $groupUserOrderId
     * @param null $userId
     * @param null $courseName
     * @param null $status
     * @param null $paymentStatus
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findOfflineCourseOrders($groupUserOrderId = null, $userId = null, $courseName = null, $status = null, $paymentStatus = null) {
        $query = $this->findGroupUserOrdersQueryBuilder(true, null, $groupUserOrderId, $userId, $courseName,  null, $status, $paymentStatus);
        $query->join('p.course', 'c')
            ->andWhere('c.isOnline = false');
        return $query;
    }

    /**
     * @param null $groupUserOrderId
     * @param null $userId
     * @param null $courseName
     * @param null $status
     * @param null $paymentStatus
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findOnlineCourseOrders($groupUserOrderId = null, $userId = null, $courseName = null, $status = null, $paymentStatus = null) {
        $query = $this->findGroupUserOrdersQueryBuilder(true, null, $groupUserOrderId, $userId, $courseName,  null, $status, $paymentStatus);
        $query->join('p.course', 'c')
            ->andWhere('c.isOnline = true')
            ->andWhere('guo.groupOrder is null');
        return $query;
    }

    /**
     * @param $productId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductGroupUserOrdersQueryBuilder($productId)
    {
        return $this->createQueryBuilder('guo')
            ->leftJoin('guo.groupOrder', 'go')
            ->where('go.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('guo.id', 'DESC');
    }

    /**
     * @param $supplierUserId
     * @param $groupUserOrderStatuses
     * @return \Doctrine\ORM\Query
     */
    public function findSupplierGroupUserOrdersQuery($supplierUserId, array $groupUserOrderStatuses) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('guo')
            ->from(GroupUserOrder::class, 'guo')
            ->innerJoin('guo.product', 'p')
            ->where('p.supplierUser = :supplierUser')
            ->setParameter('supplierUser', $supplierUserId);

        if (!empty($groupUserOrderStatuses)) {
            $query->andWhere('guo.status in (:groupUserOrderStatuses)')
                ->setParameter('groupUserOrderStatuses', $groupUserOrderStatuses);
        }

        return $query->orderBy('guo.id', 'DESC')->getQuery();
    }

    /**
     * @param null $productId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUserOrderByProduct($productId) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('guo')
            ->from(GroupUserOrder::class, 'guo')
            ->innerJoin('guo.product', 'p')
            ->where('guo.product = :product')
            ->setParameter('product', $productId);
        return $query->orderBy('guo.id', 'DESC')->getQuery()->getResult();
    }

    /**
     * @param null $productId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTableUserCount($productId,$table) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(guo) as count')
            ->from(GroupUserOrder::class, 'guo')
            ->innerJoin('guo.product', 'p')
            ->where('guo.product = :product')
            ->andWhere('guo.tableNo = :table')
            ->setParameter('product',$productId)
            ->setParameter('table', $table);
        return $query->orderBy('guo.id', 'DESC')->getQuery()->getSingleResult()['count'];
    }
}
