<?php

namespace App\Repository;

use App\Entity\ProductSimilar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProductSimilar|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductSimilar|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductSimilar[]    findAll()
 * @method ProductSimilar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductSimilarRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductSimilar::class);
    }

    /**
     * @param null $productId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductSimilarsQueryBuilder($productId = null)
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');

        if ($productId) {
            $query->where('p.product = :productId')
                ->setParameter('productId', $productId);
        }

        return $query;
    }
}
