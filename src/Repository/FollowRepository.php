<?php

namespace App\Repository;

use App\Entity\Follow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Follow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Follow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Follow[]    findAll()
 * @method Follow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    /**
     * @param $userId
     * @return array
     */
    public function findMyFollow($userId,$type,$page, $pageLimit)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('ff.id,ff.type,ff.dataId')
            ->from('App:Follow', 'ff')
            ->where('ff.userId = :userId')
            ->setParameter('userId', $userId);

        if ($type) {
            $query->andWhere('ff.type = :type')->setParameter('type', $type);
        }

        if ($page) {
            $query->setFirstResult(($page - 1) * $pageLimit);
            $query->setMaxResults($pageLimit);
        }

        return $query->getQuery()->getResult();
    }
    
}
