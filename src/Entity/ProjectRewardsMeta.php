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
    const PROJECT_REWARDS = "project_rewards";

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

    public function isVideoMeta()
    {
        return false;
    }

    public function isTokenMeta()
    {
        return false;
    }

    /**
     * @param $groupOrderRewardsRate
     * @param $groupOrderUserRewardsRate
     * @param $regularOrderRewardsRate
     * @param $regularOrderUserRewardsRate
     * @return ProjectMeta
     */
    public function setRewardsMeta($groupOrderRewardsRate, $groupOrderUserRewardsRate, $regularOrderRewardsRate, $regularOrderUserRewardsRate) {
        return $this->setMetaValue(json_encode([
            'groupOrderRewardsRate' => $groupOrderRewardsRate,
            'groupOrderUserRewardsRate' => $groupOrderUserRewardsRate,

            'regularOrderRewardsRate' => $regularOrderRewardsRate,
            'regularOrderUserRewardsRate' => $regularOrderUserRewardsRate,
        ]));
    }

    public function getGroupOrderRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['groupOrderRewardsRate'];
    }
    public function getGroupOrderUserRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['groupOrderUserRewardsRate'];
    }
    public function getRegularOrderRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['regularOrderRewardsRate'];
    }
    public function getRegularOrderUserRewardsRate() {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $metaValueArray['regularOrderUserRewardsRate'];
    }

    public function setGroupOrderRewardsRate($groupOrderRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($groupOrderRewardsRate, $metaValueArray['groupOrderUserRewardsRate'], $metaValueArray['regularOrderRewardsRate'], $metaValueArray['regularOrderUserRewardsRate']);
    }
    public function setGroupOrderUserRewardsRate($groupOrderUserRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($metaValueArray['groupOrderRewardsRate'], $groupOrderUserRewardsRate, $metaValueArray['regularOrderRewardsRate'], $metaValueArray['regularOrderUserRewardsRate']);
    }
    public function setRegularOrderRewardsRate($regularOrderRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($metaValueArray['groupOrderRewardsRate'], $metaValueArray['groupOrderUserRewardsRate'], $regularOrderRewardsRate, $metaValueArray['regularOrderUserRewardsRate']);
    }
    public function setRegularOrderUserRewardsRate($regularOrderUserRewardsRate) {
        $metaValueArray = json_decode($this->getMetaValue(), true);
        return $this->setRewardsMeta($metaValueArray['groupOrderRewardsRate'], $metaValueArray['groupOrderUserRewardsRate'], $metaValueArray['regularOrderRewardsRate'], $regularOrderUserRewardsRate);
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'metaKey' => $this->getMetaKey(),
            'groupOrderRewardsRate' => $this->getGroupOrderRewardsRate(),
            'groupOrderUserRewardsRate' => $this->getGroupOrderUserRewardsRate(),

            'regularOrderRewardsRate' => $this->getRegularOrderRewardsRate(),
            'regularOrderUserRewardsRate' => $this->getRegularOrderUserRewardsRate(),
        ];
    }
}