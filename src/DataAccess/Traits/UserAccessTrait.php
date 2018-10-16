<?php

/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-08-27
 * Time: 11:03 PM
 */

namespace App\DataAccess\Traits;

Trait UserAccessTrait
{
    /**
     * 返回符合条件的对象用户,用于首页,服务者筛选等
     *
     * @param int $userId 当前登录用户,用来做智能显示用
     * @param int|null $regionId 用户筛选对象用户的城市
     * @param int|null $skillId 用户筛选的游戏Id,为空时则返回全部技能的用户
     * @param int|null $gender 用户筛选对象用户的性别
     * @param int|null $skillLevelId 用户筛选对象的特定技能的等级,为空时则包含全部等级
     * @param int $pageNum
     * @param int|null $pageLimit
     *
     *
     * @return array $usersArray[
     *  'id' => 1,
     *  'nickname' => 'tandy',
     *  'introduction' => 'I am tandy',
     *  'skills' => [
     *      ['skillName' => '王者荣耀', 'skillLevel' => '倔强青铜',
     *              'skillTags'=>['上单爸爸', '刺客'], 'skillVoiceUrl' => 'url', 'skillImageUrl' => 'url'
     *              'skillUnitPrice' => 10, 'skillPriceUnit' => 'halfHour'
     *      ]
     *  ],
     * ]
     *
     */
    public function getUsersArray($userId, $regionId = null, $skillId = null, $gender = null, $skillLevelId = null, $pageNum, $pageLimit) {
        //TODO
    }


    /**
     * 用于搜索用户id或者用户nickname
     *
     * @param mixed $searchKey
     * @return array $usersArray
     */
    public function searchUsersArray($searchKey) {

    }


    /**
     * 用于"我的"页面,返回用户的所有基本信息
     *
     * @param int $userId 当前用户ID
     * @return array $user[
     *  'id' => 1,
     *  'nickname' => 'tandy',
     *  'chargeTotal' => 100.00,
     *  'numOfReds' => 3,
     *  'earningTotal' => 50.00
     *  'numOfRockets' => 0,
     *      ['skillName' => '王者荣耀', 'skillLevel' => '倔强青铜',
     *              'skillTags'=>['上单爸爸', '刺客'], 'skillVoiceUrl' => 'url', 'skillImageUrl' => 'url'
     *              'skillUnitPrice' => 10, 'skillPriceUnit' => 'halfHour', 'status' => 'approved'
     *      ]
     * ]
     */
    public function getUser($userId) {



    }

    /**
     * 得到用户粉丝
     * @param int $userId
     * @param int $pageNum
     * @param int|null $pageLimit
     * @return array $usersArray
     */
    public function getUserFansArray($userId, $page, $pageLimit = null) {

    }

    /**
     * 得到用户关注
     * @param int $userId
     * @param int $pageNum
     * @param int|null $pageLimit
     * @return array $usersArray[
     *  ['id' => 2,
     *  'nickname' => 'idole',
     *  'photoUrl' => 'url',
     *  'introduction' => 'i am idole',
     *  'skills' => [
     *      ['skillName' => '王者荣耀', 'skillLevel' => '倔强青铜',
     *              'skillTags'=>['上单爸爸', '刺客'], 'skillVoiceUrl' => 'url', 'skillImageUrl' => 'url'
     *              'skillUnitPrice' => 10, 'skillPriceUnit' => 'halfHour'
     *      ]
     *  ]],
     *
     * ]
     */
    public function getUserIdolsArray($userId, $page, $pageLimit = null) {

    }

    /**
     * 返回用户来访记录
     * @param $userId
     * @param int $pageNum
     * @param int|null $pageLimit
     * @return array $usersArray
     */
    public function getVisitUsersArray($userId, $page, $pageLimit = null) {

    }

    /**
     * 返回用户浏览其他用户记录
     * @param $userId
     * @param int $pageNum
     * @param int|null $pageLimit
     * @return array $usersArray
     */
    public function getUserVisitHistoryArray($userId, $page, $pageLimit = null) {

    }
}