<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2017-09-01
 * Time: 15:02
 */

namespace App\DataAccess;

use App\DataAccess\Traits\CommandMessageDataAccessTrait;
use App\DataAccess\Traits\UserAccessTrait;
use App\Entity\Dao;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class DataAccess
{
    const PAGE_LIMIT = 20;

    use CommandMessageDataAccessTrait,
        UserAccessTrait;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * DataAccess constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $this->doctrine->getManager();
    }

    /**
     * Get doctrine
     *
     * @return ManagerRegistry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * Get entityManager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Create queryBuilder
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->entityManager->createQueryBuilder();
    }

    /**
     * @param string $clazz_name
     * @param string $pk
     * @return Dao|null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getDao($clazz_name, $pk)
    {
        return $this->entityManager->find($clazz_name, $pk);
    }

    /**
     * @param string $clazz_name
     * @param $pk
     * @return null|Dao
     */
    public function getRealDao($clazz_name, $pk)
    {
        $dao = $this->getDao($clazz_name, $pk);
        $this->refresh($dao);
        return $dao;
    }

    /**
     * @param string $clazz_name
     * @param $criteria
     * @return Dao|null|object
     */
    public function getDaoBy($clazz_name, $criteria)
    {
        return $this->entityManager->getRepository($clazz_name)->findOneBy($criteria);
    }

    /**
     * @param string $clazz_name
     * @return Dao[]
     */
    public function getDaoList($clazz_name)
    {
        return $this->entityManager->getRepository($clazz_name)->findAll();
    }

    /**
     * @param string $clazz_name
     * @param $criteria
     * @param null $orderBy
     * @param null $limit
     * @param null $offset
     * @return Dao[]
     */
    public function getDaoListBy($clazz_name, $criteria, $orderBy = null, $limit = null, $offset = null)
    {
        return $this->entityManager->getRepository($clazz_name)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param Dao $dao
     */
    public function persist(Dao $dao)
    {
        $this->entityManager->persist($dao);
    }

    /**
     * @param Dao $dao
     * @return Dao|Object
     */
    public function merge(Dao $dao)
    {
        return $this->entityManager->merge($dao);
    }

    /**
     * @param Dao $dao
     */
    public function delete(Dao $dao)
    {
        $this->entityManager->remove($dao);
    }

    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * @param Dao $dao
     */
    public function refresh(Dao $dao)
    {
        $this->entityManager->refresh($dao);
    }

    public function clear()
    {
        $this->entityManager->clear();
    }

    /**
     * @param string $clazz_name
     * @return int
     */
    public function getTotal($clazz_name)
    {
        $query = $this->entityManager->createQuery('SELECT count(c) FROM ' . $clazz_name . ' c');
        return $query->getSingleScalarResult();
    }

    /**
     * @param int $count
     * @return array
     */
    private function convertNumberToArray($count)
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $i;
        }
        return $result;
    }
}