<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:06 PM
 */

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectTextMetaRepository")
 */
class ProjectTextMeta extends ProjectMeta
{
    const TEXT_GROUP_UNLOCK = 'text_group_unlock';
    const TEXT_UNLOCK_ALL = 'text_unlock_all';
    const TEXT_WATCH = 'text_watch';
    const TEXT_UPGRADE = 'text_upgrade';
    const TEXT_UNLOCK_TOGETHER = 'text_unlock_together';

    /**
     * 小程序的按钮及文案说明
     * key => memo
     */
    const TEXT_ARRAY = [
        self::TEXT_GROUP_UNLOCK => [ 'memo' => '正式用 "集Call免费解锁" ， 审核用  "集Call免费注册" ',
            'value' => '集Call免费注册'],
        self::TEXT_UNLOCK_ALL => ['memo' => '正式用 "解锁全部课程" ， 审核用  "直接注册" ',
            'value' => '直接注册'],
        self::TEXT_WATCH => ['memo' => '正式用 "观看课程" ， 审核用  "课程简介" ',
            'value' => '课程简介'],
        self::TEXT_UPGRADE => ['memo' => '正式用 "学员升级" ， 审核用  "我要注册" ',
            'value' => '我要注册'],
        self::TEXT_UNLOCK_TOGETHER => ['memo' => '正式用 "一起解锁课程" ， 审核用  "一起注册成功" ',
            'value' => '一起注册成功']
    ];


    public function isTextMeta()
    {
        return true;
    }

    public function isBannerMeta()
    {
        return false;
    }

    public function isShareMeta()
    {
        return false;
    }

    public function isNotificationMeta()
    {
        return false;
    }

    public function isRewardsMeta()
    {
        return false;
    }

    public function setTextMeta($text) {
        $this->setMetaValue($text);
        return $this;
    }

    public function getTextMeta() {
        return $this->getMetaValue();
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'textMeta' => $this->getMetaValue(),
        ];
    }
}