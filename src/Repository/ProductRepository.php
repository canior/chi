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
     * @param bool $isOnlineCourse
     * @return \Doctrine\ORM\Query
     */
    public function findActiveProductsQuery($isCourse = false, $isOnlineCourse = true)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', Product::ACTIVE);
        if ($isCourse) {
            $query->join('p.course', 'c')
                ->andWhere('c.isOnline = :isOnline')
                ->setParameter('isOnline', $isOnlineCourse);

        } else {
            $query->andWhere('p.course is null');
        }

        return $query->orderBy('p.priority', 'DESC')->addOrderBy('p.id', 'DESC')->getQuery();
    }

    /**
     * @param bool $isCourse
     * @param null $keyword
     * @param null $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findProductsQueryBuilder($isCourse = false, $keyword = null, $status = null)
    {
        $query = $this->createQueryBuilder('p');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('p.title', $literal));
            $orX->add($query->expr()->like('p.shortDescription', $literal));
            $query->andWhere($orX);
        }

        if ($isCourse) {
            $query->andWhere('p.course is not null');
        } else {
            $query->andWhere('p.course is null');
        }

        if ($status) {
            $query->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $query->orderBy('p.priority', 'DESC')->addOrderBy('p.id', 'DESC');
    }
}
