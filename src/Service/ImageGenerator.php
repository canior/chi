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
     * @param File|null $userQrFile 小程序的识别二维码，里面挂着userId和page的shareSourceId，用户识别这个二维码可以锁定推荐关系
     * @param File|null $bannerFile 产品或者平台的介绍banner
     * @param File|null $userAvatorFile 用户头像，现在我们存的是个url不知道如果是GD的话是不是要先下载下来你这里才能拼？
     * @return File|null
     */
    public static function createShareQuanBannerImage(?File $userQrFile, ?File $bannerFile, ?File $userAvatorFile = null) {
        return null;
    }
}