<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-23
 * Time: 7:39 PM
 */

namespace App\Service;



use App\Entity\File;
use Doctrine\ORM\EntityManager;
use Intervention\Image\ImageManagerStatic as Image;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\File as FileDao;

class ImageGenerator
{
    /**
     * 合并配置背景图片和用户的QR图片
     * @param ObjectManager $entityManager
     * @param File $userQrFile 小程序的识别二维码，里面挂着userId和page的shareSourceId，用户识别这个二维码可以锁定推荐关系
     * @param File|null $bannerFile 产品或者平台的介绍banner
     * @return File
     */
    public static function createShareQuanBannerImage(ObjectManager $entityManager, File $userQrFile, ?File $bannerFile) {

        if ($bannerFile == null) {
            return $userQrFile;
        }

        $banner = Image::make($bannerFile->getAbsolutePath());
        $qr = Image::make(file_get_contents($userQrFile->getAbsolutePath()));
        $banner->insert($qr, 'bottom', 0, 65);

        $fileName = uniqid() . "jpeg";
        $md5 = md5($fileName);
        $filePath = 'upload/' . File::createPathFromMD5($md5);

        $absoluteFilePath = __DIR__ . "/../../public/" . $filePath;
        if (!file_exists($absoluteFilePath)) {
            mkdir($absoluteFilePath, 0777, true);
        }

        $banner->save($absoluteFilePath . $md5 . '.jpeg');

        $fileDao = new FileDao();
        $fileDao->setUploadUser(null)
            ->setName($fileName)
            ->setType('jpeg')
            ->setSize($banner->filesize())
            ->setPath($filePath)
            ->setMd5($md5)
            ->setUploadAt(time());

        $entityManager->persist($fileDao);
        $entityManager->flush();

        return $fileDao;
    }
}