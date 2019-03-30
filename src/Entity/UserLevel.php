<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 1:35 AM
 */

namespace App\Entity;


class UserLevel
{
    const VISITOR = 'VISITOR';
    const VIP = 'VIP'; //支付了1元以上的, VIP
    const ADVANCED = 'ADVANCED'; //支付了2000， 高级VIP
    const ADVANCED2 = 'ADVANCED2'; //被人赠送的，荣耀VIP
    const ADVANCED3 = 'ADVANCED3'; //购买了120000的，特权VIP
    const PARTNER = 'PARTNER'; //合伙人


    const PARTNER_TEACHER = 'PARTNER_TEACHER';
    const SUPPLIER = 'SUPPLIER';

    public static $userLevelPriorityArray = [
        self::VISITOR =>  1,
        self::VIP => 2,
        self::ADVANCED => 3,
        self::ADVANCED2 => 4,
        self::ADVANCED3 => 5,
        self::PARTNER => 6,
    ];

    public static $userLevelTextArray = [
        self::VISITOR =>  '普通用户', //'普通用户',
        self::VIP =>  'VIP', //'购买9.9的用户',
        self::ADVANCED => '高级VIP', //'购买2000的高级用户',
        self::ADVANCED2 => '荣耀VIP', //'别人送的高级用户',
        self::ADVANCED3 => '特权VIP', //'购买12000高级用户',
        self::PARTNER => '合伙人',
    ];

    /* 推荐高级用户的佣金 */
    public static $advanceUserUpgradeRewardsArray = [
        self::ADVANCED => 600,
        self::ADVANCED2 => 600,
        self::ADVANCED3 => 600,
        self::PARTNER => 400,

        self::PARTNER_TEACHER => 100
    ];
}
