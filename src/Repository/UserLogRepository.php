<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-13
 * Time: 6:55 PM
 */

namespace App\Repository;

use App\Entity\UserLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLog[]    findAll()
 * @method UserLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserLog::class);
    }
}