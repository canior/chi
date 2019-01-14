<?php

namespace App\Repository;

use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
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
     * 返回最近的上线用户
     * @param $userId
     * @return User|null
     */
    public function findLatestShareSourceParentUser($userId): ?User
    {
        $users = $shareSourceUserRepository = $this->getEntityManager()
            ->getRepository(ShareSourceUser::class)
            ->findBy(['user' => $userId], ['id' => 'DESC'], 1);

        if (empty($users)) {
            return null;
        }

        return $users[0];
    }

    /**
     * @param null $userId
     * @param null $username
     * @param null $role
     * @param null $createdAtStart
     * @param null $createdAtEnd
     * @return QueryBuilder
     */
    public function findUsersQueryBuilder($userId = null, $username = null, $role = null, $createdAtStart = null, $createdAtEnd = null)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('u AS user')
            ->from('App:User', 'u')
            ->addSelect('SUM(us.spentTotal) AS spentTotal')
            ->addSelect('SUM(us.sharedNum) AS sharedNum')
            ->addSelect('SUM(us.childrenNum) AS childrenNum')
            ->leftJoin('u.userStatistics', 'us')
            ->groupBy('u.id')
            // order by 拼团消费总额
            ->addOrderBy('spentTotal', 'DESC')
            // order by 总分享数量
            ->addOrderBy('sharedNum', 'DESC')
            // order by 有效下线用户数量
            ->addOrderBy('childrenNum', 'DESC')
            // order by 总收益
            ->addOrderBy('u.totalRewards', 'DESC');

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

        if ($role) {
            $literal = $query->expr()->literal("%$role%");
            $query->andWhere($query->expr()->like('u.roles', $literal));
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

        return $query;
    }

    /**
     * 找到所有用户分享到的用户
     *
     * @param int $userId
     * @param null $userLevel
     * @param int $page
     * @param  int $pageLimit
     * @return ShareSourceUser[]|Collection
     */
    public function findShareUsers($userId, $userLevel, $page, $pageLimit) {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('ssu')
            ->from('App:ShareSourceUser', 'ssu')
            ->leftJoin('ssu.shareSource', 'ss')
            ->where('ss.user = :user')
            ->setParameter('user', $userId)
            ->groupBy('ssu.user')
            ->orderBy('ss.id', 'DESC');

        if ($userLevel) {
            $query->leftJoin('ssu.user', 'ssuser')
                ->andWhere('ssuser.userLevel = :userLevel')
                ->setParameter('userLevel', $userLevel);
        }

        if ($page) {
            $query->setFirstResult(($page - 1) * $pageLimit);
            $query->setMaxResults($pageLimit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $userLevel
     * @return int
     * @throws NonUniqueResultException
     */
    public function findTotalShareUsers($userId, $userLevel = null) {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('count(distinct ssu.user)')
            ->from('App:ShareSourceUser', 'ssu')
            ->leftJoin('ssu.shareSource', 'ss')
            ->where('ss.user = :user')
            ->setParameter('user', $userId);

        if ($userLevel) {
            $query->leftJoin('ssu.user', 'ssuser')
                ->andWhere('ssuser.userLevel = :userLevel')
                ->setParameter('userLevel', $userLevel);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
