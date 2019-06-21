<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 13:17
 */

namespace App\Service\Order;


use App\Entity\GroupUserOrder;
use App\Service\Config\ConfigParams;
use App\Service\Util\CommonUtil;

class OfflineTableNo
{
    /**
     * 生成桌号
     * @param GroupUserOrder $groupUserOrder
     * @return bool|int
     * @author zxqc2018
     */
    public static function getUserTable(GroupUserOrder $groupUserOrder)
    {
        //去除线上课程,产品订单
        if (!$groupUserOrder->getProduct()->isCourseProduct() || $groupUserOrder->getProduct()->getCourse()->isOnline()) {
            return false;
        }

        // 用户
        $user = $groupUserOrder->getUser();
        $userPid = $user->getParentUser()?$user->getParentUser()->getId():0;


        // 查询有多少桌
        $table_num = (int)$groupUserOrder->getProduct()->getCourse()->getTableCount();


        // 每桌人数
        $table_user_count = (int)$groupUserOrder->getProduct()->getCourse()->getTableUserCount();

        if( !$table_num || !$table_user_count ){
            return false;
        }

        // 目前人员情况
        $groupUserOrderRepository = ConfigParams::getRepositoryManager()->getRepository(GroupUserOrder::class);
        $productUserOrderBy = $groupUserOrderRepository->findUserOrderByProduct( $groupUserOrder->getProduct()->getId() );

        // 同推荐人分布情况
        $tablesUsers = [];
        $tablesParent = [];
        foreach ($productUserOrderBy as $k => $v) {

            // 人数
            if( isset($tablesUsers[$v->getTableNo()]) ){
                $tablesUsers[$v->getTableNo()]++;
            }else{
                $tablesUsers[$v->getTableNo()] = 1;
            }

            // 同推荐人
            if( $v->getUser()->getParentUser() && $v->getUser()->getParentUser()->getId() != 0 && $v->getUser()->getParentUser()->getId() == $userPid ){
                if( isset($tablesParent[$v->getTableNo()]) ){
                    $tablesParent[$v->getTableNo()]++;
                }else{
                    $tablesParent[$v->getTableNo()] = 1;
                }
            }
        }

        // 桌子情况
        $table = [];
        for ($i=1; $i <= $table_num; $i++) {
            $table[] = [
                'no'=>$i,
                'max'=>$table_user_count,
                'user'=>isset($tablesUsers[$i])?$tablesUsers[$i]:0,
                'puser'=>isset($tablesParent[$i])?$tablesParent[$i]:0,
            ];
        }

        // 去掉坐满了
        foreach ($table as  $k=>$v) {
            if( $v['user'] >= $table_user_count  ){
                unset($table[$k]);
            }
        }

        // 桌子都满了
        if( count($table) == 0 ){
            return false;
        }

        // 可以坐的桌子按最少同推荐人排序
        $arr = CommonUtil::sortArrByManyField($table, 'puser', SORT_ASC, 'user', SORT_ASC, 'no', SORT_ASC);


        // 取最少推荐人 人数最少的桌子
        $table_num = $table[0]['no'] ??  0;

        return $table_num;
    }
}