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
    const PRODUCT_REWARDS = "product_rewards";

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

    public function setCaptainRewardsRate($captainRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($captainRewardsRate, $metaValueArray['joinerRewardsRate'], $metaValueArray['regularRewardsRate'], $metaValueArray['userRewardsRate']);
    }

    public function getJoinerRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['joinerRewardsRate'];
    }

    public function setJoinerRewardsRate($joinerRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($metaValueArray['captainRewardsRate'], $joinerRewardsRate, $metaValueArray['regularRewardsRate'], $metaValueArray['userRewardsRate']);
    }

    public function getRegularRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['regularRewardsRate'];
    }

    public function setRegularRewardsRate($regularRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($metaValueArray['captainRewardsRate'], $metaValueArray['joinerRewardsRate'], $regularRewardsRate, $metaValueArray['userRewardsRate']);
    }

    public function getUserRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['userRewardsRate'];
    }

    public function setUserRewardsRate($userRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($metaValueArray['captainRewardsRate'], $metaValueArray['joinerRewardsRate'], $metaValueArray['regularRewardsRate'], $userRewardsRate);
    }

    /**
     * @param $captainRewardsRate
     * @param $joinerRewardsRate
     * @param $regularRewardsRate
     * @param $userRewardsRate
     * @return ProjectMeta
     */
    public function setRewardsMeta($captainRewardsRate, $joinerRewardsRate, $regularRewardsRate, $userRewardsRate) {
        return $this->setMetaValue(json_encode([
            'captainRewardsRate' => $captainRewardsRate,
            'joinerRewardsRate' => $joinerRewardsRate,
            'regularRewardsRate' => $regularRewardsRate,
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
            'regularRewardsRate' => $this->getRegularRewardsRate(),
            'userRewardsRate' => $this->getUserRewardsRate(),
        ];
    }

}