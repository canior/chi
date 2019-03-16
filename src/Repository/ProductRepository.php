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
     * @param bool $isCourse
     * @return \Doctrine\ORM\Query
     */
    public function findActiveProductsQuery($isCourse = true)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::ACTIVE);

        if ($isCourse) {
            $query->leftJoin('p.course', 'c')
                ->orderBy('c.endDate', 'DESC');
        } else {
            $query->orderBy('p.id', 'DESC');
        }

        return $query->getQuery();
    }

    /**
     * @param null $keyword
     * @param null $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductsQueryBuilder($keyword = null, $status = null)
    {
        $query = $this->createQueryBuilder('p');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('p.title', $literal));
            $orX->add($query->expr()->like('p.shortDescription', $literal));
            $query->andWhere($orX);
        }

        if ($status) {
            $query->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $query->orderBy('p.id', 'DESC');
    }
}
