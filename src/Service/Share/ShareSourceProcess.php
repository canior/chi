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

            switch ($type) {
                case ShareSource::GZH:
                    $shareSource = ShareSource::factoryNew($type, $contentType, $page, $user, null, $referProductShare->getShareTitle(), $product);
                    CommonUtil::entityPersist($shareSource);
                    $res[$shareSource->getCombineKey()] = $shareSource->getArray();
                    break;
                case ShareSource::GZH_QUAN:
                    $shareSource = ShareSource::factoryNew($type, $contentType, $page, $user, null, null, $product);
                    /**
                     * @var QrCode $qrCode
                     */
                    $qrCode = DependencyInjectionSingletonConfig::getInstance()->getQrCodeFactory()->create($shareSource->getPage(), [
                        'size' => 110,
                        'round_block_size' => 0,
                    ]);
                    $bannerFile = ImageGenerator::createGzhShareQuanBannerImage(ConfigParams::getRepositoryManager(), $qrCode, $product->getShareImageFile());
                    $shareSource->setBannerFile($bannerFile);
                    CommonUtil::entityPersist($shareSource);
                    $res[$shareSource->getCombineKey()] = $shareSource->getArray();
                    break;
            }
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