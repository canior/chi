<?php

namespace App\Repository;

use App\Entity\ProductReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @param null $keyword
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductReviewsQueryBuilder($keyword = null)
    {
        $query = $this->createQueryBuilder('pr');
        $query->innerJoin('pr.product', 'p');
        $query->addSelect('COUNT(pr.id) AS total');
//        $query->addSelect('COUNT(pr.id) AS total');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('p.title', $literal));
            $orX->add($query->expr()->like('p.shortDescription', $literal));
            $query->andWhere($orX);
        }

        return $query->orderBy('p.id', 'DESC');
    }
}
