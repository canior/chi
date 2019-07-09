<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 13:17
 */

namespace App\Service\Order;


use App\Entity\GroupUserOrder;
use App\Entity\User;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use App\Service\Config\ConfigParams;

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
        ConfigParams::getLogger()->info('生成桌号  时间：' .date('Y-m-d H:i:s',time()).' 订单号：'.$groupUserOrder->getId());

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
        /**
         * @var GroupUserOrder[] $productUserOrderBy
         */
        $productUserOrderBy = FactoryUtil::groupUserOrderRepository()->findUserOrderByProduct( $groupUserOrder->getProduct()->getId() );

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
        $table_no = $arr[0]['no'] ??  0;

        return $table_no;
    }

    /**
     * 补发用户的桌号
     * @param User $user
     * @return bool
     * @author zxqc2018
     */
    public static function supplySystemTableNo(User $user)
    {
        $res = false;

        if ($user->isSystemSubjectPrivilege()) {
            $groupUserOrderRepository = FactoryUtil::groupUserOrderRepository();
            //是否有报名了但是没有分配桌号的
            $notDistributeOrders = $groupUserOrderRepository->findBy(['user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
            if (!empty($notDistributeOrders)) {
                foreach ($notDistributeOrders as $notDistributeOrder) {
                    if (!empty($notDistributeOrder->getPaymentTime()) && $notDistributeOrder->getProduct()->isCourseProduct() &&
                        !$notDistributeOrder->getProduct()->getCourse()->isOnline() && $notDistributeOrder->getProduct()->getCourse()->isSystemSubject() &&
                        empty($notDistributeOrder->getTableNo())) {
                        $notDistributeOrder->setTableNo((int)OfflineTableNo::getUserTable($notDistributeOrder));
                        $notDistributeOrder->setCheckStatus(GroupUserOrder::CHECK_PASS);
                        $notDistributeOrder->setCheckAt(time());
                        CommonUtil::entityPersist($notDistributeOrder);
                        //todo sms通知
                        $res = true;
                    }
                }
            }
        }

        return $res;
    }
}