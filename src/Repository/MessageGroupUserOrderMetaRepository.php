<?php
/**
 * User: Jeff
 * Date: 2019-06-26
 */

namespace App\Repository;

use App\Entity\MessageGroupUserOrderMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\GroupUserOrder;
use App\Entity\Follow;
use Doctrine\ORM\Query\Expr;
/**
 * @method MessageGroupUserOrderMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageGroupUserOrderMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageGroupUserOrderMeta[]    findAll()
 * @method MessageGroupUserOrderMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageGroupUserOrderMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MessageGroupUserOrderMeta::class);
    }

    /**
     * @param $userId
     * @return array
     */
    public function getGroupUserOrder($userId,$checkStatus)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('cc,ff.id,ff.title,ff.content,ff.createdAt,ff.isRead')
            ->from(MessageGroupUserOrderMeta::class, 'ff')
            ->leftJoin(GroupUserOrder::class,'cc',Expr\Join::WITH,'ff.dataId = cc.id')
            ->where('ff.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('ff.id', 'DESC');

        if( $checkStatus ){
            $query->andWhere('cc.checkStatus = :checkStatus')->setParameter('checkStatus', $checkStatus);
        }

        return $query->getQuery();
    }

    /**
     * @param $userId
     * @return array
     */
    public function setMessagesIsRead($idsArray)
    {

        if( !count($idsArray) ){
            return true;
        }

        $q = $this->_em->createQueryBuilder('u')
            ->update(MessageGroupUserOrderMeta::class, 'u')
            ->set('u.isRead', 1)
            ->where('u.id in (:idsArray)')
            ->setParameter('idsArray', $idsArray);
        return $q->getQuery()->execute();
    }
}