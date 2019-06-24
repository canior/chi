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
     * @param bool $isSimpleSelector 是否只取得生成联动字段
     * @return array
     * @author zxqc2018
     */
    public function getCategoryTree($cid, $isSimpleSelector = false)
    {
        $res = [];
        $where = [
            'isDeleted' => false
        ];
        //子类和父类关系数组
        $sonRefParentArr = [];

        $arrayMethod = $isSimpleSelector ? 'getSimpleArray' : 'getArray';
        $list = $this->findBy($where);
        if (!empty($list)) {
            foreach ($list as $row) {
                if (is_null($row->getParentCategory())) {
                    $res[$row->getId()] = $row->$arrayMethod();
                } else {
                    $sonRefParentArr[$row->getId()] = $row->getParentCategory()->getId();
                }
            }

            foreach ($list as $row) {
                if (!is_null($row->getParentCategory()) && isset($res[$row->getParentCategory()->getId()])) {
                    if (!$row->isSingleCourse()) {
                        $res[$row->getParentCategory()->getId()]['children'][] = $row->$arrayMethod();
                    }
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
     * 取得产品类别
     * @param null|int $parentId 父类ID
     * @param string $name 分类名
     * @param bool $isSingleCourse 是否单课程
     * @param null $showFreeZone 是否免费专区
     * @param null $showRecommendZone 是否推荐专区
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findCategoryListQuery($parentId = null, $name = '', $isSingleCourse = false, $showFreeZone = null, $showRecommendZone = null)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.isDeleted =:isDeleted')
            ->setParameter('isDeleted', false);

        if (!is_null($parentId)) {
            if (empty($parentId)) {
                $query->andWhere('c.parentCategory is null');
            } else {
                $query->andWhere('c.parentCategory =:parentId')
                    ->setParameter('parentId', $parentId);
            }
        }

        if (!empty($name)) {
            $query->andWhere('c.name like :name')
                ->setParameter('name', "%{$name}%");
        }

        if (!is_null($isSingleCourse)) {
            $query->andWhere('c.singleCourse =:singleCourse')
                ->setParameter('singleCourse', $isSingleCourse);
        }

        if (!is_null($showFreeZone)) {
            $query->andWhere('c.showFreeZone =:showFreeZone')
                ->setParameter('showFreeZone', $showFreeZone);
        }

        if (!is_null($showRecommendZone)) {
            $query->andWhere('c.showRecommendZone =:showRecommendZone')
                ->setParameter('showRecommendZone', $showRecommendZone);
        }

        $query->addOrderBy('c.priority', 'DESC')->addOrderBy('c.id', 'DESC');
        return $query;
    }

    /**
     * 取得免费类别
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findFreeCategory()
    {
        return $this->findCategoryListQuery(null, '', null, true, null);
    }

    /**
     * 取得推荐类别
     * @return \Doctrine\ORM\QueryBuilder
     * @author zxqc2018
     */
    public function findRecommendCategory()
    {
        return $this->findCategoryListQuery(null, '', null, null, true);
    }
}
