<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 1:44 AM
 */

namespace App\Entity;


class Subject
{
    const THINKING = 'THINKING';
    const TRADING = 'TRADING';
    const SYSTEM_1 = 'SYSTEM_1';
    const SYSTEM_2 = 'SYSTEM_2';

    public static $subjectTextArray = [
        self::THINKING => '变现思维课',
        self::TRADING => '变现交易课',
        self::SYSTEM_1 => '变现系统课I',
        self::SYSTEM_2 => '变现系统课II',
    ];

    public static $subjectBianxianUserLevelArray = [
        self::THINKING => BianxianUserLevel::THINKING,
        self::TRADING => BianxianUserLevel::THINKING,
        self::SYSTEM_1 => BianxianUserLevel::ADVANCED,
        self::SYSTEM_2 => BianxianUserLevel::ADVANCED,
    ];


    /**
     * 科目需要特定的会员等级才能上或者看
     * @var array
     */
    public static $subjectUserLevelConstraintArray = [
        self::THINKING => [BianxianUserLevel::VISITOR, BianxianUserLevel::THINKING, BianxianUserLevel::ADVANCED, BianxianUserLevel::PARTNER, BianxianUserLevel::DISTRIBUTOR],
        self::TRADING => [BianxianUserLevel::VISITOR, BianxianUserLevel::THINKING, BianxianUserLevel::ADVANCED, BianxianUserLevel::PARTNER, BianxianUserLevel::DISTRIBUTOR],
        self::SYSTEM_1 => [BianxianUserLevel::ADVANCED, BianxianUserLevel::PARTNER, BianxianUserLevel::DISTRIBUTOR],
        self::SYSTEM_2 => [BianxianUserLevel::ADVANCED, BianxianUserLevel::PARTNER, BianxianUserLevel::DISTRIBUTOR],
    ];

    public static $subjectRewards = [
        self::SYSTEM_1 => [
            BianxianUserLevel::PARTNER => 10000,
            BianxianUserLevel::DISTRIBUTOR => 10000,
            'THINKING_TEACHER' => 1000,
        ],
        self::SYSTEM_2 => [
            BianxianUserLevel::PARTNER => 10000,
            BianxianUserLevel::DISTRIBUTOR => 10000,
            'THINKING_TEACHER' => 1000,
        ],
    ];
}
