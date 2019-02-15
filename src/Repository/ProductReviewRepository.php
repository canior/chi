<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductReview[]    findAll()
 * @method ProductReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductReviewRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductReview::class);
    }

    /**
     * @param $productId
     * @return \Doctrine\ORM\Query
     */
    public function findActiveProductReviewsQuery($productId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('pr')
            ->from(ProductReview::class, 'pr')
            ->leftJoin(Product::class, 'p')
            ->where('pr.status = :status')
            ->setParameter('status', ProductReview::ACTIVE)
            ->andWhere('p.id = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('pr.id', 'desc');
        return $query->getQuery();
    }

    /**
     * @param $userId
     * @param int $page
     * @param int $pageLimit
     * @return ProductReview[]
     */
    public function findUserProductReviews($userId, $page = 1, $pageLimit = 5)
    {
        $query = $this->createQueryBuilder('pr');
        $query->leftJoin('pr.groupUserOrder', 'guo')
            ->where('guo.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('pr.id', 'DESC')
            ->setFirstResult(($page - 1) * $pageLimit)
            ->setMaxResults($pageLimit);
        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findUserProductReviewsTotal($userId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(pr.id) AS total')
            ->from('App:ProductReview', 'pr')
            ->leftJoin('pr.groupUserOrder', 'guo')
            ->where('guo.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param null $userId
     * @param null $productId
     * @param null $keyword
     * @return \Doctrine\ORM\QueryBuilder
     *  [
     *      'product' => Product Object
     *      'totalReviewed' => int
     *      'favorableRate' => int
     *      'lastReviewedAt' => int
     *  ]
     */
    public function findReviewedProductsQueryBuilder($userId = null, $productId = null, $keyword = null)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('p AS product')
            ->from('App:Product', 'p')
            ->leftJoin('p.productReviews', 'pr')
            ->where('pr.id IS NOT NULL')
            ->addSelect('COUNT(pr.id) AS totalReviewed')
            // 好评率： rate = 1-5 分， 5分*总数 / 所有评论的总分
            ->addSelect('SUM(CASE WHEN pr.rate = 5 THEN pr.rate ELSE 0 END) / SUM(pr.rate) AS favorableRate')
            ->addSelect('MAX(pr.createdAt) AS lastReviewedAt')
            ->groupBy('p.id')
            ->orderBy('p.id', 'DESC');

        if ($userId) {
            $query->leftJoin('pr.groupUserOrder', 'guo')
                ->andWhere('guo.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($productId) {
            $query->andWhere('p.id = :productId')
                ->setParameter('productId', $productId);
        }

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('p.title', $literal));
            $orX->add($query->expr()->like('p.shortDescription', $literal));
            $query->andWhere($orX);
        }

        return $query;
    }

    /**
     * @param $productId
     * @return mixed
     *  [
     *      'totalReviewed' => int
     *      'totalRate1' => int
     *      'totalRate2' => int
     *      'totalRate3' => int
     *      'totalRate4' => int
     *      'totalRate5' => int
     *      'favorableRate' => int
     *  ]
     *
     * @throws NonUniqueResultException If the query result is not unique.
     * @throws NoResultException        If the query returned no result and hydration mode is not HYDRATE_SINGLE_SCALAR.
     */
    public function findProductReviewStatistics($productId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(pr.id) AS totalReviewed')
            ->addSelect('SUM(CASE WHEN pr.rate = 1 THEN 1 ELSE 0 END) AS totalRate1')
            ->addSelect('SUM(CASE WHEN pr.rate = 2 THEN 1 ELSE 0 END) AS totalRate2')
            ->addSelect('SUM(CASE WHEN pr.rate = 3 THEN 1 ELSE 0 END) AS totalRate3')
            ->addSelect('SUM(CASE WHEN pr.rate = 4 THEN 1 ELSE 0 END) AS totalRate4')
            ->addSelect('SUM(CASE WHEN pr.rate = 5 THEN 1 ELSE 0 END) AS totalRate5')
            ->addSelect('SUM(CASE WHEN pr.rate = 5 THEN pr.rate ELSE 0 END) / SUM(pr.rate) AS favorableRate')
            ->from('App:ProductReview', 'pr')
            ->where('pr.product = :productId')
            // 好评率： rate = 1-5 分， 5分*总数 / 所有评论的总分
            ->setParameter('productId', $productId);

        return $query->getQuery()->getSingleResult();
    }

    /**
     * @param bool $isCourse
     * @param null $productId
     * @param null $rate
     * @param null $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductReviewsQueryBuilder($isCourse = false, $productId = null, $rate = null, $status = null)
    {
        $query = $this->createQueryBuilder('pr')
            ->orderBy('pr.id', 'DESC');

        if ($productId) {
            $query->andwhere('pr.product = :productId')
                ->setParameter('productId', $productId);
        }

        if ($rate != null) {
            $query->andWhere('pr.rate = :rate')
                ->setParameter('rate', $rate);
        }

        if ($status) {
            $query->andWhere('pr.status = :status')
                ->setParameter('status', $status);
        }

        if ($isCourse) {
            $query->leftJoin('pr.product', 'p')
                ->andWhere('p.course is not null');
        } else {
            $query->leftJoin('pr.product', 'p')
                ->andWhere('p.course is null');
        }

        return $query;
    }
}
