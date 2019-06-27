<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\UserAccountOrder;

/**
 * @method UserAccountOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAccountOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAccountOrder[]    findAll()
 * @method UserAccountOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAccountOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAccountOrder::class);
    }

    
    /**
     * 佣金总额
     * @param $user
     * @return array
     */
    public function getUserCommissionAmount($user){
    	$query = $this->getEntityManager()->createQueryBuilder()
            ->select('sum(uao.amount) as count')
            ->from('App:UserAccountOrder', 'uao')
            ->where('uao.userAccountOrderType != :userAccountOrderType')
            ->andWhere('uao.user = :user')
            ->setParameter('userAccountOrderType', UserAccountOrder::WITHDRAW)
            ->setParameter('user', $user->getId());
        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * 资金明细
     * @param $user
     * @return array
     */
    public function getUserAccountOrders($user){
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('uao')
            ->from('App:UserAccountOrder', 'uao')
            ->where('uao.user = :user')
            ->setParameter('user', $user->getId());
        return $query->getQuery();
    }
}
