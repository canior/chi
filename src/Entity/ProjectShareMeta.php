<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 2:24 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectShareMetaRepository")
 */
class ProjectShareMeta extends ProjectMeta
{

    const GROUP_ORDER = 'groupOrder';
    const PRODUCT = 'product';
    const USER = 'user';

    public static $scenes = [
        self::GROUP_ORDER => '拼团分享',
        self::PRODUCT => '产品分享',
        self::USER => '用户分享',
    ];

    public function isTextMeta()
    {
        return false;
    }

    public function isBannerMeta()
    {
        return false;
    }

    public function isShareMeta()
    {
        return true;
    }

    public function isNotificationMeta()
    {
        return false;
    }

    public function isRewardsMeta()
    {
        return false;
    }

    /**
     * @param $scene
     * @param $type
     * @param $title
     * @param $bannerFileId
     * @param $bannerEditable
     * @return ProjectMeta
     */
    public function setShareMeta($scene, $type, $title, $bannerFileId, $bannerEditable) {
        return $this->setMetaValue(json_encode([
            'scene' => $scene,
            'type' => $type,
            'title' => $title,
            'bannerFileId' => $bannerFileId,
            'bannerEditable' => $bannerEditable
        ]));
    }

    public function getShareScene() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['scene'];
    }

    public function getShareSceneText() {
        return isset(self::$scenes[$this->getShareScene()]) ? self::$scenes[$this->getShareScene()] : $this->getShareScene();
    }

    public function getShareType() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['type'];
    }

    public function getShareTitle() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['title'];
    }

    public function setShareTitle($title) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setShareMeta($metaValueArray['scene'], $metaValueArray['type'], $title, $metaValueArray['bannerFileId'], $metaValueArray['bannerEditable']);
    }

    public function getShareBannerFileId() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['bannerFileId'];
    }

    public function setShareBannerFileId($bannerFileId) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setShareMeta($metaValueArray['scene'], $metaValueArray['type'], $metaValueArray['title'], $bannerFileId, $metaValueArray['bannerEditable']);
    }

    public function isBannerEditable() {
        if ($this->getShareScene() == self::USER)
            return true;
        return false;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'scene' => $this->getShareScene(),
            'type' => $this->getShareType(),
            'title' => $this->getShareTitle(),
            'bannerFileId' => $this->getShareBannerFileId(),
            'bannerEditable' => $this->isBannerEditable()
        ];
    }
}