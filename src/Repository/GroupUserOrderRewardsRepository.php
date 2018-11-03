<?php

namespace App\Repository;

use App\Entity\GroupUserOrderRewards;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupUserOrderRewards|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupUserOrderRewards|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupUserOrderRewards[]    findAll()
 * @method GroupUserOrderRewards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupUserOrderRewardsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupUserOrderRewards::class);
    }


    /**
     * 用户的所有下线用户, 下线意味着支付完成，必然有一张group_user_order_rewards
     *
     * @param int $userId
     * @param boolean|null $valid 是否有效下线, null则返回全部
     * @param int $page
     * @param int $pageLimit
     *
     * @return QueryBuilder
     */
    public function findSubUsers($userId, $valid = null, $page = 1, $pageLimit = 10) {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('u.id')
            ->addSelect('u.nickname')
            ->addSelect('u.avatarUrl')
            ->addSelect('u.lastLogin')
            ->addSelect('SUM(o.total) AS totalOrderAmount')
            ->addSelect('SUM(r.userRewards) AS totalUserRewards')
            ->addSelect('count(r.id) AS userRewardsOrderNum')
            ->from('App:GroupUserOrderRewards', 'r')
            ->leftJoin('r.groupUserOrder', 'o')
            ->leftJoin('o.user', 'u')
            ->andWhere('r.user = :userId ')
            ->setParameter('userId', $userId)
            ->groupBy('r.user');

        if ($valid) {
            $query->leftJoin('o.user', 'sb')
                ->andWhere('sb.parentUser = :parentUser')
                ->setParameter('parentUser', $userId);
        }

        return $query->getQuery()->getResult();

    }
}
