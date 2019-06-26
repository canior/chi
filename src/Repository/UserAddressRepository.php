<?php

namespace App\Repository;

use App\Entity\UserAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAddress[]    findAll()
 * @method UserAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAddressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAddress::class);
    }

    
    public function setAllAddressNotDefault($userId)
    {
        $q = $this->_em->createQueryBuilder('u')
            ->update(UserAddress::class, 'u')
            ->set('u.isDefault', '0')
            ->where('u.user == :user')
            ->setParameter('user', $userId);
        return $q->getQuery()->execute();
    }

}
