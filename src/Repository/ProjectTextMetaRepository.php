<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:20 PM
 */

namespace App\Repository;

use App\Entity\ProjectTextMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectTextMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectTextMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectTextMeta[]    findAll()
 * @method ProjectTextMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectTextMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectTextMeta::class);
    }

    /**
     * @param null|string $keyword
     * @return QueryBuilder
     */
    public function findTextMetaQueryBuilder($keyword = null)
    {
        $query = $this->createQueryBuilder('ptm')
            ->orderBy('ptm.id', 'DESC');

        if ($keyword) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$keyword%");
            $orX->add($query->expr()->like('ptm.memo', $literal));
            $orX->add($query->expr()->like('ptm.metaKey', $literal));
            $orX->add($query->expr()->like('ptm.metaValue', $literal));
            $query->andWhere($orX);
        }

        return $query;
    }
}