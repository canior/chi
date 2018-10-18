<?php

namespace App\Repository;

use App\DataAccess\DataAccess;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param int $page
     * @param int $pageLimit
     * @return Product[] Returns an array of Product objects
     */
    public function findActiveProducts($page = 1, $pageLimit = DataAccess::PAGE_LIMIT)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::ACTIVE);

        if ($page) {
            $query->setFirstResult(($page - 1) * $pageLimit);
            $query->setMaxResults($pageLimit);
        }

        return $query->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param null $keyword
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductsQueryBuilder($keyword = null)
    {
        $query = $this->createQueryBuilder('p');

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
