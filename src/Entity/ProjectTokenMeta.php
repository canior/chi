<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 22:13
 */

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectTokenMetaRepository")
 */
class ProjectTokenMeta extends ProjectMeta
{
    const JQ_GZH_WX_TOKEN = "jq_gzh_wx_token";
    const JQ_GZH_WX_JS_TOKEN = "jq_gzh_wx_js_token";

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
        return false;
    }

    public function isTokenMeta()
    {
        return true;
    }

    /**
     *
     * @param $token
     * @param $expiredAt
     * @return ProjectMeta
     * @author zxqc2018
     */
    public function setProjectTokenMeta($token, $expiredAt)
    {
        return $this->setMetaValue(json_encode([
            'token' => $token,
            'expiresAt' => $expiredAt,
        ]));
    }

    public function getToken()
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        if ($this->isExpired()) {
            return '';
        }
        return $metaValueArray['token'] ?? '';
    }

    public function setToken(?string $token)
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setProjectTokenMeta($token, $metaValueArray['expiresAt']);
    }


    public function getExpiresAt()
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['expiresAt'] ?? '';
    }

    public function setExpiresAt(?int $expiresAt)
    {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setProjectTokenMeta($metaValueArray['token'], $expiresAt);
    }

    /**
     * 是否过期
     * @return bool
     * @author zxqc2018
     */
    public function isExpired()
    {
        return time() + 600 > $this->getExpiresAt();
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'memo' => $this->getMemo(),
            'token' => $this->getToken(),
            'expiresAt' => $this->getExpiresAt(),
        ];
    }
}