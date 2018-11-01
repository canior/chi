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

    const HOME_BANNER_1 = "home_banner_1";
    const HOME_BANNER_2 = "home_banner_2";
    const HOME_BANNER_3 = "home_banner_3";

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

    public function getRedirectUrl() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['redirectUrl'];
    }

    public function getBannerFileId() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['bannerFileId'];
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