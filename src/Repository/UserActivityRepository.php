<?php

namespace App\Repository;

use App\Entity\UserActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserActivity[]    findAll()
 * @method UserActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserActivityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserActivity::class);
    }

    /**
     * @param null $userId
     * @param null $keyword
     * @param null $createdAtStart
     * @param null $createdAtEnd
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUserActivitiesQueryBuilder($userId = null, $keyword = null, $createdAtStart = null, $createdAtEnd = null)
    {
        $query = $this->createQueryBuilder('ua')
            ->orderBy('ua.id', 'DESC');

        if ($userId) {
            $query->where('ua.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($keyword) {
            $literal = $query->expr()->literal("%$keyword%");
            $query->andWhere($query->expr()->like('ua.page', $literal));
        }

        if ($createdAtStart) {
            if (is_string($createdAtStart)) {
                $createdAtStart = strtotime($createdAtStart);
            }
            $query->andWhere('ua.createdAt >= :createdAtStart')
                ->setParameter('createdAtStart', $createdAtStart);
        }

        if ($createdAtEnd) {
            if (is_string($createdAtEnd)) {
                $createdAtEnd = strtotime($createdAtEnd);
            }
            $query->andWhere('ua.createdAt <= :createdAtEnd')
                ->setParameter('createdAtEnd', $createdAtEnd);
        }

        return $query;
    }
}
