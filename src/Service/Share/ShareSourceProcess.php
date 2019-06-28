<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/28
 * Time: 21:14
 */

namespace App\Service\Share;


use App\Entity\Product;
use App\Entity\ProjectShareMeta;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;

class ShareSourceProcess
{
    /**
     * 返回分享数据
     * @param $type
     * @param $contentType
     * @param User $user
     * @param Product $product
     * @param $page
     * @return \App\Service\ResultData
     */
    public function createShareSource($type, $contentType, User $user, Product $product, $page)
    {
        $res = CommonUtil::resultData();
        if (is_null($page)) {
            $page = '';
        }
        /**
         * @var ProjectShareMeta $referProductShare
         */
        $referProductShare = FactoryUtil::projectShareMetaRepository()->findShareMeta($type, $contentType);

        //产品信息页面转发分享
        $referShareSource = FactoryUtil::shareSourceRepository()->findOneBy([
            'user'=> $user,
            'product' => $product,
            'type' => $type,
            'contentType' => $contentType,
        ]);
        if ($referShareSource == null) {
            $referShareSource = ShareSource::factoryNew($type, $contentType, $page, $user, null, $referProductShare->getShareTitle(), $product);
            CommonUtil::entityPersist($referShareSource);
        }

        $res[$referShareSource->getCombineKey()] = $referShareSource->getArray();


        return $res;
    }

    /**
     * 记录用户分享
     * @param string $shareSourceId
     * @param User $user
     * @author zxqc2018
     */
    public function addShareSourceUser($shareSourceId, User $user)
    {
        $shareSource = FactoryUtil::shareSourceRepository()->find($shareSourceId);

        if (!empty($shareSource)) {

            $shareSourceUser = ShareSourceUser::factory($shareSource, $user);
            $shareSource->addShareSourceUser($shareSourceUser);
            CommonUtil::entityPersist($shareSource);
        }
    }
}