<?php

namespace App\Repository;

use App\Entity\ProjectMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectMeta[]    findAll()
 * @method ProjectMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectMeta::class);
    }

    /**
     * @param null|string $keyword
     * @return QueryBuilder
     */
    public function findMetas($keyword = null)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('m')
            ->from('App\Entity\ProjectMeta', 'm');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('m.metaKey', $literal));
            $orX->add($query->expr()->like('m.metaValue', $literal));
            $query->andWhere($orX);
        }

        return $query;
    }

//    /**
//     * @return ProjectMeta[] Returns an array of ProjectMeta objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProjectMeta
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
