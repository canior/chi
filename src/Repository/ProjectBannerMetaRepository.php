<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:13 PM
 */

namespace App\Repository;

use App\Entity\ProjectBannerMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectBannerMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectBannerMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectBannerMeta[]    findAll()
 * @method ProjectBannerMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectBannerMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectBannerMeta::class);
    }

    /**
     * @param null|string $keyword
     * @return QueryBuilder
     */
    public function findBannerMetaQueryBuilder($keyword = null)
    {
        $query = $this->createQueryBuilder('pbm')
            ->orderBy('pbm.id', 'DESC');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('pbm.memo', $literal));
            $orX->add($query->expr()->like('pbm.metaKey', $literal));
            $orX->add($query->expr()->like('pbm.metaValue', $literal));
            $query->andWhere($orX);
        }

        return $query;
    }
}