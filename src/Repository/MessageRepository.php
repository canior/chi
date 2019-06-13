<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
    public function findMessageQuery($userId,$isRead = '')
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('ms')
            ->from(Message::class, 'ms')
            ->where('ms.user = :userId')
            ->setParameter('userId', $userId);

        if( $isRead ){
            $query->andWhere('ms.isRead = :isRead')->setParameter('isRead', $isRead);
        }
        
        $query->orderBy('ms.id', 'DESC');

        return $query;
    }
}
