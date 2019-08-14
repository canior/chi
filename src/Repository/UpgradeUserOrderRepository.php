<?php

namespace App\Repository;

use App\Entity\UpgradeUserOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @method UpgradeUserOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpgradeUserOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpgradeUserOrder[]    findAll()
 * @method UpgradeUserOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpgradeUserOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UpgradeUserOrder::class);
    }


    /**
     * @param int|null $id
     * @param int|null $userId
     * @param string|null $name
     * @param string|null $oldUserLevel
     * @param string|null $userLevel
     * @param string|null $status
     * @return QueryBuilder
     */
    public function search($id = null, $userId = null, $name = null, $oldUserLevel = null, $userLevel = null, $status = null) {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('uuo AS UpgradeUserOrder')
            ->from('App:UpgradeUserOrder', 'uuo')
            ->leftJoin('uuo.user', 'u');

        if ($id) {
            $query->andWhere('uuo.id = :id')
                ->setParameter('id', $id);
        }

        if ($userId) {
            $query->andWhere('u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($name) {
            $query->andWhere('u.name like :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($oldUserLevel) {
            $query->andWhere('uuo.oldUserLevel = :oldUserLevel')
                ->setParameter('oldUserLevel', $oldUserLevel);
        }

        if ($userLevel) {
            $query->andWhere('uuo.userLevel = :userLevel')
                ->setParameter('userLevel', $userLevel);
        }

        if ($status) {
            $query->andWhere('uuo.status = :status')
                ->setParameter('status', $status);
        }

        $query->orderBy('uuo.id', 'desc');

        return $query;
    }
}
