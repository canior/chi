<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 6:34 PM
 */

namespace App\Repository;

use App\Entity\ProjectRewardsMeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectRewardsMeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectRewardsMeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectRewardsMeta[]    findAll()
 * @method ProjectRewardsMeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRewardsMetaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectRewardsMeta::class);
    }
}