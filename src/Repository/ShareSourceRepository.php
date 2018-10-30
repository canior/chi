<?php

namespace App\Repository;

use App\Entity\ShareSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShareSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareSource[]    findAll()
 * @method ShareSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareSourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShareSource::class);
    }

    /**
     * @param null $userId
     * @param null $username
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findShareSourcesQueryBuilder($userId = null, $username = null)
    {
        $query = $this->createQueryBuilder('ss')
            ->orderBy('ss.id', 'DESC');

        if ($userId) {
            $query->where('ss.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($username) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$username%");
            $orX->add($query->expr()->like('u.username', $literal));
            $orX->add($query->expr()->like('u.nickname', $literal));
            $query->leftJoin('ss.user', 'u')
                ->andWhere($orX);
        }

        return $query;
    }

    /**
     * @param $userId
     * @return
     *   [
     *      [
     *          'shareSource' => ShareSource Object
     *          'totalUsers' => int
     *      ],
     *      ...
     *   ]
     */
    public function findShareSources($userId)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('ss AS shareSource')
            ->from('App:ShareSource', 'ss')
            ->addSelect('COUNT(DISTINCT(ssu.user)) AS totalUsers')
            ->leftJoin('ss.shareSourceUsers', 'ssu')
            ->where('ss.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('ss.user');
        return $query->getQuery()->getResult();
    }
}
