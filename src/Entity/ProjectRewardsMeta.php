<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 6:31 PM
 */

namespace App\Entity;


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
        // TODO: Implement getArray() method.
    }

}