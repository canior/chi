<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:13 PM
 */

namespace App\Repository;

use App\Entity\FollowTeacherMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FollowTeacherMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method FollowTeacherMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method FollowTeacherMeta[]    findAll()
 * @method FollowTeacherMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowTeacherMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FollowTeacherMeta::class);
    }

    

}