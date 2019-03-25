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

    public static $userLevelTextArray = [
        self::VISITOR =>  '普通用户', //'普通用户',
        self::VIP =>  'VIP', //'普通用户',
        self::ADVANCED => '高级VIP', //'高级用户',
        self::ADVANCED2 => '荣耀VIP', //'高级用户',
        self::ADVANCED3 => '特权VIP', //'高级用户',
        self::PARTNER => '合伙人',
    ];

    /* 学员身份的名额 */
    public static $userLevelRecommanderStockArray = [
        self::VISITOR =>  0,
        self::VIP =>  0,
        self::ADVANCED => 0,
        self::ADVANCED2 => 0,
        self::ADVANCED3 => 0,
        self::PARTNER => 1000,
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
