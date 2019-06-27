<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/18
 * Time: 15:12
 */

namespace App\Entity;
use App\Service\Ali\AliCommon;
use App\Service\Ali\AliVod;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectVideoMetaRepository")
 */
class ProjectVideoMeta extends ProjectMeta
{
    const VIDEO_FREE_ZONE = "video_free_zone";

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

    public function isVideoMeta()
    {
        return true;
    }

    public function isTokenMeta()
    {
        return false;
    }
    /**
     * @param string $aliyunVideoId
     * @param string $aliyunVideoUrl
     * @param string $aliyunVideoImageUrl
     * @param string $aliyunVideoExpiresAt
     * @return ProjectMeta
     * @author zxqc2018
     */
    public function setVideoMeta($aliyunVideoId, $aliyunVideoUrl, $aliyunVideoImageUrl, $aliyunVideoExpiresAt)
    {
        return $this->setMetaValue(json_encode([
            'aliyunVideoId' => $aliyunVideoId,
            'aliyunVideoUrl' => $aliyunVideoUrl,
            'aliyunVideoImageUrl' => $aliyunVideoImageUrl,
            'aliyunVideoExpiresAt' => $aliyunVideoExpiresAt,
        ]));
    }

    public function getAliyunVideoId()
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['aliyunVideoId'];
    }

    public function setAliyunVideoId(?string $aliyunVideoId)
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setVideoMeta($aliyunVideoId, $metaValueArray['aliyunVideoUrl'], $metaValueArray['aliyunVideoImageUrl'], $metaValueArray['aliyunVideoExpiresAt']);
    }

    public function getAliyunVideoUrl()
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['aliyunVideoUrl'];
    }

    public function setAliyunVideoUrl(?string $aliyunVideoUrl)
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setVideoMeta($metaValueArray['aliyunVideoId'], $aliyunVideoUrl, $metaValueArray['aliyunVideoImageUrl'], $metaValueArray['aliyunVideoExpiresAt']);
    }

    public function getAliyunVideoImageUrl()
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['aliyunVideoImageUrl'];
    }

    public function setAliyunVideoImageUrl(?string $aliyunVideoImageUrl)
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setVideoMeta($metaValueArray['aliyunVideoId'], $metaValueArray['aliyunVideoUrl'], $aliyunVideoImageUrl, $metaValueArray['aliyunVideoExpiresAt']);
    }

    public function getAliyunVideoExpiresAt()
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['aliyunVideoExpiresAt'];
    }

    public function setAliyunVideoExpiresAt(?string $aliyunVideoExpiresAt)
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setVideoMeta($metaValueArray['aliyunVideoId'], $metaValueArray['aliyunVideoUrl'], $metaValueArray['aliyunVideoImageUrl'], $aliyunVideoExpiresAt);
    }

    /**
     * 视频是否过期
     * @return bool
     * @author zxqc2018
     */
    public function isAliyunVideoExpired() {
        return time() + 600 > $this->getAliyunVideoExpiresAt();
    }

    /**
     * 刷新阿里云视频地址
     * @return int
     */
    public function refreshAliyunVideo()
    {
        if (empty($this->getAliyunVideoId())) {
            return 0;
        }

        if (!$this->isAliyunVideoExpired()) {
            return 1;
        }

        try {
            $ali = new AliCommon();
            $playInfo = $ali->getPlayInfo($this->getAliyunVideoId());

            $aliyunVideoUrl = AliVod::getVideoUrl($playInfo);
            $aliyunVideoImageUrl = AliVod::getVideoImageUrl($playInfo);
            $aliyunVideoExpiresAt = AliVod::getVideoExpiresAt($playInfo);

            $this->setAliyunVideoUrl($aliyunVideoUrl);
            $this->setAliyunVideoImageUrl($aliyunVideoImageUrl);
            $this->setAliyunVideoExpiresAt($aliyunVideoExpiresAt);

            return 2;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'memo' => $this->getMemo(),
            'aliyunVideoId' => $this->getAliyunVideoId(),
            'aliyunVideoUrl' => $this->getAliyunVideoUrl(),
            'aliyunVideoImageUrl' => $this->getAliyunVideoImageUrl(),
            'aliyunVideoExpiresAt' => $this->getAliyunVideoExpiresAt(),
        ];
    }
}