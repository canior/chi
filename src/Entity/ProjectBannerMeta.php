<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:07 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectBannerMetaRepository")
 */
class ProjectBannerMeta extends ProjectMeta
{
    const BANNER_HOME_1 = "banner_home_1";
    const BANNER_HOME_2 = "banner_home_2";
    const BANNER_HOME_3 = "banner_home_3";
    const BANNER_HOME_4 = "banner_home_4";
    const BANNER_LOGIN = 'banner_login';
    const BANNER_MY_SHARE = 'banner_my_share';
    const BANNER_PRODUCT = 'banner_product';
    const BANNER_HOME_FREE_ZONE = 'banner_home_free_zone';

    /**
     * key => memo
     */
    const BANNERS_ARRAY = [
        self::BANNER_HOME_1 => '主页的横幅1',
        self::BANNER_HOME_2 => '主页的横幅2',
        self::BANNER_HOME_3 => '主页的横幅3',
        self::BANNER_HOME_4 => '主页的横幅4',
        self::BANNER_LOGIN => '登录页面的横幅',
        self::BANNER_MY_SHARE => '我的分享横幅，介绍成功分享10个高级学员能得免费成为变现商学院的高级会员',
        self::BANNER_PRODUCT => '介绍购买产品升级到高级会员免费听课的banner',
        self::BANNER_HOME_FREE_ZONE => '首页免费专区',
    ];


    public function isTextMeta()
    {
        return false;
    }

    public function isBannerMeta()
    {
        return true;
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

    public function getRedirectUrl() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['redirectUrl'];
    }

    public function setRedirectUrl($redirectUrl) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setBannerMeta($metaValueArray['bannerFileId'], $redirectUrl);
    }

    public function getBannerFileId() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['bannerFileId'];
    }

    public function setBannerFileId($bannerFileId) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setBannerMeta($bannerFileId, $metaValueArray['redirectUrl']);
    }

    /**
     * @param $bannerFileId
     * @param $redirectUrl
     * @return ProjectMeta
     */
    public function setBannerMeta($bannerFileId, $redirectUrl) {
        $metaValue = json_encode(['bannerFileId' => $bannerFileId, 'redirectUrl' => $redirectUrl]);
        return $this->setMetaValue($metaValue);
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'bannerFileId' => $this->getBannerFileId(),
            'redirectUrl' => $this->getRedirectUrl()
        ];
    }
}