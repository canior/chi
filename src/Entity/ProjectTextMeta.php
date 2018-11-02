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