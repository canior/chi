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
    const BANNER_LOGIN = 'banner_login';
    const BANNER_USER_UPGRADE = 'banner_user_upgrade';
    const BANNER_GROUP_ORDER_INFO = "banner_group_order_info";

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