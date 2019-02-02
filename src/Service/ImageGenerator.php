<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-23
 * Time: 7:39 PM
 */

namespace App\Service;



use App\Entity\File;

class ImageGenerator
{
    /**
     * TODO
     * 合并配置背景图片和用户的QR图片
     * @param File|null $userQrFile
     * @param File|null $bannerFile
     * @return File|null
     */
    public static function createShareQuanBannerImage(?File $userQrFile, ?File $bannerFile) {
        return null;
    }
}