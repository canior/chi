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
use App\Service\Config\ConfigParams;
use App\Service\Config\DependencyInjectionSingletonConfig;
use App\Service\ImageGenerator;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Endroid\QrCode\QrCode;

class ShareSourceProcess
{
    /**
     * 返回分享数据
     * @param array|string $types
     * @param $contentType
     * @param User $user
     * @param Product $product
     * @param $page
     * @return \App\Service\ResultData
     */
    public function createShareSource($types, $contentType, User $user, Product $product, $page)
    {
        $res = CommonUtil::resultData();
        if (is_null($page)) {
            $page = '';
        }

        $types = CommonUtil::myExplode($types);

        foreach ($types as $type) {
            /**
             * @var ProjectShareMeta $referProductShare
             */
            $referProductShare = FactoryUtil::projectShareMetaRepository()->findShareMeta($type, $contentType);

            //产品信息页面转发分享
            $shareSource = FactoryUtil::shareSourceRepository()->findOneBy([
                'user'=> $user,
                'product' => $product,
                'type' => $type,
                'contentType' => $contentType,
            ]);

            if ($shareSource == null) {
                switch ($type) {
                    case ShareSource::GZH:
                        $shareSource = ShareSource::factoryNew($type, $contentType, $page, $user, null, $referProductShare->getShareTitle(), $product);
                        CommonUtil::entityPersist($shareSource);
                        break;
                    case ShareSource::GZH_QUAN:
                        $shareSource = ShareSource::factoryNew($type, $contentType, $page, $user, null, null, $product);
                        /**
                         * @var QrCode $qrCode
                         */
                        $qrCode = DependencyInjectionSingletonConfig::getInstance()->getQrCodeFactory()->create($page);
                        $bannerFile = ImageGenerator::createGzhShareQuanBannerImage(ConfigParams::getRepositoryManager(), $qrCode, $product->getShareImageFile());
                        $shareSource->setBannerFile($bannerFile);
                        CommonUtil::entityPersist($shareSource);
                        break;
                }
            }

            $res[$shareSource->getCombineKey()] = $shareSource->getArray();
        }


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