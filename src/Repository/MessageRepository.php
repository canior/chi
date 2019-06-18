<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\GroupUserOrder;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @param int $userId
     * @param int $courseId
     * @param bool $isOnline
     * @return QueryBuilder
     */
    public function findOrderMessageQuery($userId,$dataType)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('ms')
            ->from(Message::class, 'ms')
            ->leftJoin(GroupUserOrder::class, 'guo', 'ms.dataId = guo.id')
            ->where('ms.dataType = :dataType')
            ->andWhere('ms.user = :userId')
            ->setParameter('dataType',$dataType)
            ->setParameter('userId', $userId);

        $query->orderBy('ms.id', 'DESC');
        return $query;
    }
}
