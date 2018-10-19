<?php

namespace App\Repository;

use App\Entity\CommandMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CommandMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandMessage[]    findAll()
 * @method CommandMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommandMessage::class);
    }

    /**
     * @param int $limit
     * @return CommandMessage[]|null
     */
    public function getNextGroupOfCommandMessages($limit = 10)
    {
        $query = $this->createQueryBuilder('m');
        $query->where('m.status = :status')
            ->andWhere('m.multithread = :multithread')
            ->setParameter('status', CommandMessage::PENDING)
            ->setParameter('multithread', 0)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $roomId
     * @param $type
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isCommandMessageExistBy($roomId, $type)
    {
        $query = $this->createQueryBuilder('m');

        $literal = $query->expr()->literal("{\"roomId\":$roomId}");
        $query->where($query->expr()->eq('m.commandData', $literal));

        $commandClass = $type == 'AutoOpen' ? 'AppBundle\Command\Enqueue\AutoOpenRoomCommand' : 'AppBundle\Command\Enqueue\AutoCloseRoomCommand';
        $query->andWhere('m.commandClass = :commandClass')
            ->setParameter('commandClass', $commandClass);

        return $query->getQuery()->getOneOrNullResult();
    }
}
