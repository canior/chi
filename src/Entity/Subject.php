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

    /**
     * 科目需要特定的会员等级才能上
     * @var array
     */
    public static $subjectUserLevelConstraintArray = [
        self::THINKING => [UserLevel::VISITOR, UserLevel::ADVANCED, UserLevel::PARTNER],
        self::TRADING => [UserLevel::VISITOR, UserLevel::ADVANCED, UserLevel::PARTNER],
        self::SYSTEM_1 => [UserLevel::ADVANCED, UserLevel::PARTNER],
        self::SYSTEM_2 => [UserLevel::ADVANCED, UserLevel::PARTNER],
    ];

    /* 课上成交老师的佣金 */
    public static $teacherRewards = [
        self::THINKING => [
            UserLevel::ADVANCED => 500,
            UserLevel::PARTNER => 5000
        ],
        self::TRADING => [
            UserLevel::ADVANCED => 500,
            UserLevel::PARTNER => 5000
        ],
        self::SYSTEM_1 => [
            UserLevel::PARTNER => 4000
        ],
        self::SYSTEM_2 => [
            UserLevel::PARTNER => 4000
        ],
    ];

    /* 曾经上过课的老师的佣金 */
    public static $oldTeacherRewards = [
        self::TRADING => [ //current subject
            self::THINKING => [ //old subject
                UserLevel::ADVANCED => 500,
                UserLevel::PARTNER => 3000
            ]
        ],
        self::SYSTEM_1 => [
            self::THINKING => [
                UserLevel::PARTNER => 3000
            ],
            self::TRADING => [
                UserLevel::PARTNER => 3000
            ],
        ],
        self::SYSTEM_2 => [
            self::THINKING => [
                UserLevel::PARTNER => 3000
            ],
            self::TRADING => [
                UserLevel::PARTNER => 3000
            ],
        ],
    ];

}