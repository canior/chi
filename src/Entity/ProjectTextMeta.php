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
    const UPGRADE_USER_TEXT = 'upgrade_user_text';

    /**
     * 小程序的按钮及文案说明
     * key => memo
     */
    const TEXT_ARRAY = [
        self::UPGRADE_USER_TEXT => [ 'memo' => '正式用 "介绍升级用户的文字" ， 审核用  "空值" ',
            'value' => '集Call免费注册'],
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