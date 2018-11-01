<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 6:31 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRewardsMetaRepository")
 */
class ProjectRewardsMeta extends ProjectMeta
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
        return false;
    }

    public function isNotificationMeta()
    {
        return false;
    }

    public function isRewardsMeta()
    {
        return true;
    }

    public function getCaptainRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['captainRewardsRate'];
    }

    public function getJoinerRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['joinerRewardsRate'];
    }

    public function getRegularRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['regularRewardsRate'];
    }

    public function getUserRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['userRewardsRate'];
    }

    /**
     * @param $captainRewardsRate
     * @param $joinerRewardsRate
     * @param $regularOrderRewardsRate
     * @param $userRewardsRate
     * @return ProjectMeta
     */
    public function setRewardsMeta($captainRewardsRate, $joinerRewardsRate, $regularOrderRewardsRate, $userRewardsRate) {
        return $this->setMetaValue(json_encode([
            'captainRewardsRate' => $captainRewardsRate,
            'joinerRewardsRate' => $joinerRewardsRate,
            'regularOrderRewardsRate' => $regularOrderRewardsRate,
            'userRewardsRate' => $userRewardsRate,
        ]));
    }


    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'captainRewardsRate' => $this->getCaptainRewardsRate(),
            'joinerRewardsRate' => $this->getJoinerRewardsRate(),
            'regularOrderRewardsRate' => $this->getRegularRewardsRate(),
            'userRewardsRate' => $this->getUserRewardsRate(),
        ];
    }

}