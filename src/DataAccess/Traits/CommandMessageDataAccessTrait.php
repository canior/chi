<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 09:09
 */

namespace App\DataAccess\Traits;

use App\Entity\CommandMessage;
use Doctrine\ORM\QueryBuilder;

trait CommandMessageDataAccessTrait
{
    /**
     * @param int $limit
     * @return CommandMessage[]|null
     */
    public function getNextGroupOfCommandMessages($limit = 10)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = $this->createQueryBuilder();

        $query->select('m')
            ->from('AppBundle:CommandMessage', 'm')
            ->where('m.status = :status')
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
        /**
         * @var QueryBuilder $query
         */
        $query = $this->createQueryBuilder();
        $query->select('m')
            ->from('AppBundle:CommandMessage', 'm');

        $literal = $query->expr()->literal("{\"roomId\":$roomId}");
        $query->where($query->expr()->eq('m.commandData', $literal));

        $commandClass = $type == 'AutoOpen' ? 'AppBundle\Command\Enqueue\AutoOpenRoomCommand' : 'AppBundle\Command\Enqueue\AutoCloseRoomCommand';
        $query->andWhere('m.commandClass = :commandClass')
            ->setParameter('commandClass', $commandClass);

        return $query->getQuery()->getOneOrNullResult();
    }
}