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
    const ADVANCED = 'ADVANCED';
    const PARTNER = 'PARTNER';
    const PARTNER_TEACHER = 'PARTNER_TEACHER';
    const SUPPLIER = 'SUPPLIER';

    public static $userLevelTextArray = [
        self::VISITOR =>  '普通用户', //'普通用户',
        self::ADVANCED => '高级VIP', //'高级用户',
        self::PARTNER => '合伙人',
    ];

    /* 学员身份的名额 */
    public static $userLevelRecommanderStockArray = [
        self::VISITOR => 0,
        self::ADVANCED => 0,
        self::PARTNER => 1000,
    ];

    /* 推荐高级用户的佣金 */
    public static $advanceUserUpgradeRewardsArray = [
        self::ADVANCED => 400,
        self::PARTNER => 600,
        self::PARTNER_TEACHER => 100
    ];
}