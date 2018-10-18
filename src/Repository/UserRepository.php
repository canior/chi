<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param null $userId
     * @param null $username
     * @param null $loginTimeStart
     * @param null $loginTimeEnd
     * @param null $createdAtStart
     * @param null $createdAtEnd
     * @return QueryBuilder
     */
    public function findUsers($userId = null, $username = null, $loginTimeStart = null, $loginTimeEnd = null, $createdAtStart = null, $createdAtEnd = null)
    {
        /**
         * @var QueryBuilder $query
         */
//        $query = $this->getEntityManager()->createQueryBuilder();
//        $query->select('u')
//            ->from('App\Entity\User', 'u');
        $query = $this->createQueryBuilder('u');

        if ($userId) {
            $query->where('u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($username) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$username%");
            $orX->add($query->expr()->like('u.username', $literal));
            $orX->add($query->expr()->like('u.nickname', $literal));
            $query->andWhere($orX);
        }

        if ($loginTimeStart) {
            $query->andWhere('u.lastLogin >= :loginTimeStart')
                ->setParameter('loginTimeStart', $loginTimeStart);
        }

        if ($loginTimeEnd) {
            $query->andWhere('u.lastLogin <= :loginTimeEnd')
                ->setParameter('loginTimeEnd', $loginTimeEnd);
        }

        if ($createdAtStart) {
            if (is_string($createdAtStart)) {
                $createdAtStart = strtotime($createdAtStart);
            }
            $query->andWhere('u.createdAt >= :createdAtStart')
                ->setParameter('createdAtStart', $createdAtStart);
        }

        if ($createdAtEnd) {
            if (is_string($createdAtEnd)) {
                $createdAtEnd = strtotime($createdAtEnd);
            }
            $query->andWhere('u.createdAt <= :createdAtEnd')
                ->setParameter('createdAtEnd', $createdAtEnd);
        }

        // TODO: order by 拼团消费总额
        // order by 总分享数量
        $query->leftJoin('u.userShares', 'us');
        $query->addOrderBy('count(us)', 'DESC');
        $query->addGroupBy('u.id');
        // order by 有效下线用户数量
        $query->leftJoin('u.subUsers', 'su');
        $query->addOrderBy('count(su)', 'DESC');
        // order by 总收益
        $query->addOrderBy('u.totalRewards', 'DESC');

        return $query;
    }
}
