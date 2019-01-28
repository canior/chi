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
     * @param string $scene 分享的场景
     * @param string $type quan 或者 refer
     * @param string $title 分享出去的标题
     * @param int|null $bannerFileId 分享出去的背景图片
     * @param bool $bannerEditable 背景图片是否可编辑
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
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['bannerEditable'];
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