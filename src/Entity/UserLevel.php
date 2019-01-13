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

    public static $userLevelTextArray = [
        self::VISITOR => '普通学员',
        self::ADVANCED => '高级学员',
        self::PARTNER => '合伙人',
    ];

    /* 学员身份的推荐资格 */
    public static $userLevelRecommanderStock = [
        self::VISITOR => 0,
        self::ADVANCED => 0,
        self::PARTNER => 100
    ];

    /* 推荐人的佣金 */
    public static $userLevelRecommanderRewards = [
        self::VISITOR => 0,
        self::ADVANCED => 9000,
        self::PARTNER => 30000
    ];
}