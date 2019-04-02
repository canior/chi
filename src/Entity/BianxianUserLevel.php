<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-03-25
 * Time: 1:21 PM
 */

namespace App\Entity;


class BianxianUserLevel
{
    const VISITOR = 'VISITOR';
    const THINKING = 'THINKING';
    const ADVANCED = 'ADVANCED';
    const PARTNER = 'PARTNER';
    const DISTRIBUTOR = 'DISTRIBUTOR';

    public static $userLevelTextArray = [
        self::VISITOR => '普通学员',
        self::THINKING => '思维学员',
        self::ADVANCED => '系统学员',
        self::PARTNER => '合伙人',
        self::DISTRIBUTOR => '分院',
    ];

    public static $userLevelPriorityArray = [
        self::VISITOR => 1,
        self::THINKING => 2,
        self::ADVANCED => 3,
        self::PARTNER => 4,
        self::DISTRIBUTOR => 5
    ];

    /* 学员身份的推荐资格 */
    public static $userLevelPriceArray = [
        self::VISITOR => 0,
        self::THINKING => 0,
        self::ADVANCED => 10000,
        self::PARTNER => 100000,
        self::DISTRIBUTOR => 600000,
    ];


    /* 学员身份的推荐资格 */
    public static $userLevelRecommanderStockArray = [
        self::VISITOR => 0,
        self::THINKING => 0,
        self::ADVANCED => 0,
        self::PARTNER => 100,
        self::DISTRIBUTOR => 600,
    ];

}
