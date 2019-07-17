<?php

namespace App\Repository;

use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Entity\UserLevel;
use App\Form\UserAddressType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\CourseStudent;
use App\Entity\UserAddress;

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
     * @param null $nameWildCard
     * @param null $role
     * @param null $userLevel
     * @param null $bianxianUserLevel
     * @param null $createdAtStart
     * @param null $createdAtEnd
     * @return QueryBuilder
     */
    public function findUsersQueryBuilder($userId = null, $nameWildCard = null, $role = null, $userLevel = null, $bianxianUserLevel = null, $createdAtStart = null, $createdAtEnd = null, $recommanderName = null)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('u AS user')
            ->from('App:User', 'u');

        if ($userId) {
            $query->where('u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($role) {
            $query->andWhere('u.roles like :roles')
                ->setParameter('roles', '%' . $role . '%');
        }

        if ($recommanderName) {
            $query->andWhere('u.recommanderName like :recommanderName')
                ->setParameter('recommanderName', '%' . $recommanderName . '%');
        }

        if ($userLevel) {
            $query->andWhere('u.userLevel = :userLevel')
                ->setParameter('userLevel', $userLevel);
        }

        if ($bianxianUserLevel) {
            $query->andWhere('u.bianxianUserLevel = :bianxianUserLevel')
                ->setParameter('bianxianUserLevel', $bianxianUserLevel);
        }

        if ($nameWildCard) {
            $orX = $query->expr()->orX();
            $literal = $query->expr()->literal("%$nameWildCard%");
            $orX->add($query->expr()->like('u.username', $literal));
            $orX->add($query->expr()->like('u.nickname', $literal));
            $orX->add($query->expr()->like('u.name', $literal));
            $query->andWhere($orX);
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
     * @param int $userId
     * @param int $courseId
     * @param bool $isOnline
     * @return QueryBuilder
     */
    public function findCourseStudentQuery($userId, $courseId = null, $isOnline = true)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('cs')
            ->from(CourseStudent::class, 'cs')
            ->innerJoin('cs.studentUser', 'u')
            ->innerJoin('cs.course', 'c')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId);

        if ($courseId) {
            $query->andWhere('c.id = :courseId')
            ->setParameter('courseId', $courseId);
        }
        if ($isOnline != null) {
            $query->andWhere('c.isOnline = :isOnline')
                ->setParameter('isOnline', $isOnline);
        }

        $query->groupBy('c');
        $query->orderBy('cs.id', 'DESC');

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
    public function findShareUsers($userId, $userLevel, $page, $pageLimit)
    {
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
    public function findTotalShareUsers($userId, $userLevel = null)
    {
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


    /**
     * 初始化数据使用
     * 返回有名额>0的用户
     * @return QueryBuilder
     */
    public function findUserWithRecommandStocks()
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.recommandStock > 0');

        return $query;
    }
}
