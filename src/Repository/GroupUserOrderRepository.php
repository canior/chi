<?php

namespace App\Repository;

use App\Entity\GroupUserOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Product;

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
    public function findGroupUserOrdersQueryBuilder($isCourse = false, $groupOrderId = null, $groupUserOrderId = null, $userId = null, $productName = null, $type = null, $status = null, $paymentStatus = null, $username = null, $createdAtStart = null, $createdAtEnd = null,$subject = NULL,$recommanderName = NULL)
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

        if ($username) {
            $query->leftJoin('guo.user', 'gu');
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$username%");
            $orX->add($query->expr()->like('gu.username', $literal));
            $orX->add($query->expr()->like('gu.nickname', $literal));
            $orX->add($query->expr()->like('gu.name', $literal));
            $query->andWhere($orX);
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

        if ($createdAtStart) {
            if (is_string($createdAtStart)) {
                $createdAtStart = strtotime($createdAtStart);
            }
            $query->andWhere('guo.createdAt >= :createdAtStart')
                ->setParameter('createdAtStart', $createdAtStart);
        }

        if ($createdAtEnd) {
            if (is_string($createdAtEnd)) {
                $createdAtEnd = strtotime($createdAtEnd);
            }
            $query->andWhere('guo.createdAt <= :createdAtEnd')
                ->setParameter('createdAtEnd', $createdAtEnd);
        }

        if ($subject) {
            $query->andWhere('c.subject =:subject')
                ->setParameter('subject', $subject);
        }

        if ($recommanderName) {
            $query->leftJoin('guo.user', 'guser');
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$recommanderName%");
            $orX->add($query->expr()->like('guser.recommanderName', $literal));
            $query->andWhere($orX);
        }

        return $query;
    }

    /**
     * @param bool $isCourse
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function groupUserOrdersQueryBuilder($productId = null,$paymentStatus = null,$isGetCount = false)
    {
        $query = $this->createQueryBuilder('guo')
            ->orderBy('guo.id', 'DESC');

        if ($productId) {
            $query->where('guo.product = :productId')
            ->setParameter('productId', $productId);
        }

        if ($paymentStatus) {
            $query->andWhere('guo.paymentStatus = :paymentStatus')
                ->setParameter('paymentStatus', $paymentStatus);
        }

        if ($isGetCount) {
            $query->select('count(guo.id)');
            return $query;
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
    public function findOfflineCourseOrders($groupUserOrderId = null, $userId = null, $courseName = null, $status = null, $paymentStatus = null, $username = null, $createdAtStart = null, $createdAtEnd = null,$subject = null,$recommanderName = null) {
        $query = $this->findGroupUserOrdersQueryBuilder(true, null, $groupUserOrderId, $userId, $courseName,  null, $status, $paymentStatus, $username, $createdAtStart, $createdAtEnd,$subject,$recommanderName);
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
    public function findSupplierGroupUserOrdersQuery($supplierUserId, array $groupUserOrderStatuses, $page, $pageLimit) {
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

        if ($page) {
            $query->setFirstResult(($page - 1) * $pageLimit);
            $query->setMaxResults($pageLimit);
        }

        return $query->orderBy('guo.id', 'DESC')->getQuery();
    }

    /**
     * @param $supplierUserId
     * @param $groupUserOrderStatuses
     * @return \Doctrine\ORM\Query
     */
    public function supplierGroupUserOrders($supplierUserId, array $groupUserOrderStatuses, $page, $pageLimit) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('guo')
            ->from(GroupUserOrder::class, 'guo')
            ->innerJoin('guo.product', 'p')
            ->where('p.supplierUser = :supplierUser')
            ->andWhere('p.course is null')
            ->setParameter('supplierUser', $supplierUserId);

        if (!empty($groupUserOrderStatuses)) {
            $query->andWhere('guo.status in (:groupUserOrderStatuses)')
                ->setParameter('groupUserOrderStatuses', $groupUserOrderStatuses);
        }

        if ($page) {
            $query->setFirstResult(($page - 1) * $pageLimit);
            $query->setMaxResults($pageLimit);
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

    /**
     * @param null $productId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProductUserCount($productId,$paymentStatus = 'paid' ) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(guo) as count')
            ->from(GroupUserOrder::class, 'guo')
            ->where('guo.product = :product')
            ->andWhere('guo.paymentStatus = :paymentStatus')
            ->setParameter('product',$productId)
            ->setParameter('paymentStatus',$paymentStatus);
        return $query->orderBy('guo.id', 'DESC')->getQuery()->getSingleResult()['count'];
    }


    /**
     * @param $productId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUserGroupUserOrders($where,$isGetCount = false)
    {
        $query = $this->createQueryBuilder('guo')
            ->leftJoin('guo.product', 'p')
            ->leftJoin('p.course', 'c');

        //过滤返回金专用订单
        $query->andWhere('p.title != :title')->setParameter('title',Product::BACK_PRODUCT_TITLE);

        if (isset($where['userId'])) {
            $query->andWhere('guo.user = :userId')->setParameter('userId',$where['userId']);
        }

        if (isset($where['recommanders'])) {
            $query->andWhere('guo.user in (:recommanders)')->setParameter('recommanders',$where['recommanders']);
        }

        if (isset($where['status'])) {
            $query->andWhere('guo.status in (:status)')->setParameter('status',$where['status']);
        }

        if (isset($where['paymentStatus'])) {
            $query->andWhere('guo.paymentStatus in (:paymentStatus)')->setParameter('paymentStatus',$where['paymentStatus']);
        }

        if (isset($where['isCourseProduct']) ) {
            if( $where['isCourseProduct'] == true ){
                $query->andWhere('p.course is not null');
            }else{
                $query->andWhere('p.course is null');
            }
        }

        if (isset($where['isOnline'])) {
            if( $where['isOnline'] == true ){
                $query->andWhere('c.isOnline = :isOnline')->setParameter('isOnline',1);
            }else{
                $query->andWhere('c.isOnline = :isOnline')->setParameter('isOnline',0);
            }
        }

        if (isset($where['recommanders'])) {
            $query->groupBy('p.id');
        }

        if ($isGetCount) {
            $query->select('count(guo.id)');
            return $query;
        }

        // dump( $query );die;

        return $query->orderBy('guo.id', 'DESC');
    }
}
