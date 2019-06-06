<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 19:40
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * 查询分类树[只支持一二级]
     * @param int|null $cid
     * @return array
     * @author zxqc2018
     */
    public function getCategoryTree($cid)
    {
        $res = [];
        $where = [];
        //子类和父类关系数组
        $sonRefParentArr = [];

        $list = $this->findBy($where);
        if (!empty($list)) {
            foreach ($list as $row) {
                if (is_null($row->getParentCategory())) {
                    $res[$row->getId()] = $row->getArray();
                } else {
                    $sonRefParentArr[$row->getId()] = $row->getParentCategory()->getId();
                }
            }

            foreach ($list as $row) {
                if (!is_null($row->getParentCategory()) && isset($res[$row->getParentCategory()->getId()])) {
                    $res[$row->getParentCategory()->getId()]['subCategoryList'][] = $row->getArray();
                }
            }
        }

        if (!empty($cid)) {
            //子类型转换成父类别
            if (isset($sonRefParentArr[$cid])) {
                $cid = $sonRefParentArr[$cid];
            }
            return $res[$cid] ?? [];
        }
        //去除数字key 返回json数组格式
        sort($res);
        return $res;
    }

    /**
     * @param $parentId
     * @param string $name
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findCategoryListQuery($parentId, $name = '')
    {
        $query = $this->createQueryBuilder('c');
        if (empty($parentId)) {
            $query->where('c.parentCategory is null');
        } else {
            $query->where('c.parentCategory =:parentId')
                ->setParameter('parentId', $parentId);
        }

        if (!empty($name)) {
            $query->andWhere('c.name like :name')
                ->setParameter('name', "%{$name}%");
        }

        return $query;
    }
}
