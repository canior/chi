<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 2:25 PM
 */

namespace App\Repository;

use App\Entity\ProjectShareMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectShareMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectShareMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectShareMeta[]    findAll()
 * @method ProjectShareMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectShareMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectShareMeta::class);
    }

    /**
     * @param null|string $keyword
     * @return QueryBuilder
     */
    public function findShareMetaQueryBuilder($keyword = null)
    {
        $query = $this->createQueryBuilder('psm')
            ->orderBy('psm.id', 'DESC');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('psm.memo', $literal));
            $orX->add($query->expr()->like('psm.metaKey', $literal));
            $orX->add($query->expr()->like('psm.metaValue', $literal));
            $query->andWhere($orX);
        }

        return $query;
    }
}