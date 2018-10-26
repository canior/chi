<?php

namespace App\Repository;

use App\Entity\ShareSourceUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShareSourceUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareSourceUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareSourceUser[]    findAll()
 * @method ShareSourceUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareSourceUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShareSourceUser::class);
    }

    /**
     * @param $userId
     * @return ShareSourceUser[]
     */
    public function findUnderlingUsers($userId)
    {
        // TODO: fix this query
        return $this->createQueryBuilder('ssu')
            ->addSelect('SUM(guor.userRewards) AS userRewardsTotal')
            ->leftJoin('ssu.shareSource', 'ss')
            ->leftJoin('App\Entity\GroupUserOrder', 'guo', 'WITH', 'guo.user = ssu.user')
            ->leftJoin('guo.groupUserOrderRewards', 'guor')
            ->where('ss.user = :userId')
            ->andWhere('guor.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('ssu.user')
            ->getQuery()->getResult();
    }
}
