<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:13 PM
 */

namespace App\Repository;

use App\Entity\FollowCourseMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FollowCourseMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method FollowCourseMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method FollowCourseMeta[]    findAll()
 * @method FollowCourseMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowCourseMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FollowCourseMeta::class);
    }

        /**
     * @param $userId
     * @return array
     */
    public function findMyFollow($userId,$type,$page, $pageLimit)
    {
        // $query = $this->getEntityManager()->createQueryBuilder()
        //     ->select('ff AS Follow')
        //     ->from('App:Follow', 'ff');
            // ->leftJoin('ff.dataId', 'u');


        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('ff')
            ->from('App:Follow', 'ff')
            ->leftJoin('ff.dataId', 'c')
            ->where('ff.user = :userId')
            ->setParameter('userId', $userId);

        if ($type) {
            $query->andWhere('c.isOnline = :isOnline')->setParameter('isOnline', 1);
        }

        if ($page) {
            $query->setFirstResult(($page - 1) * $pageLimit);
            $query->setMaxResults($pageLimit);
        }

        return $query->getQuery()->getResult();
    }
    

}