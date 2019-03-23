<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-14
 * Time: 12:33 AM
 */

namespace App\Controller\Api;

use App\Entity\Course;
use App\Entity\CourseOrder;
use App\Entity\Teacher;
use App\Entity\UpgradeOrderCoupon;
use App\Entity\User;
use App\Entity\UpgradeUserOrder;
use App\Entity\ShareSource;

use App\Repository\FileRepository;
use App\Repository\TeacherRepository;
use App\Repository\UpgradeOrderCouponRepository;
use App\Repository\UserRepository;
use App\Service\Wx\WxCommon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use App\Entity\ProjectShareMeta;
use App\Service\ImageGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use SplFileObject;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/wxapi")
 */
class TestController extends BaseController
{
    /**
     * @Route("/testYadi", name="testYadi", methods="GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request) {
        if ($this->getEnvironment() != 'dev') exit;

//        /**
//         * @var UpgradeOrderCouponRepository $upgradeOrderCouponRepository
//         */
//        $upgradeOrderCouponRepository = $this->getEntityManager()->getRepository(UpgradeOrderCoupon::class);
//        $coupons = $upgradeOrderCouponRepository->createCoupons(5);
//        for ($i = 0; $i < sizeof($coupons); $i++) {
//            $coupon = $coupons[$i];
//            echo $coupon . '<br/>';
//        }
//
//        exit;
        $user1 = $this->getEntityManager()->getRepository(User::class)->find(7);
        var_dump($user1->getArray());
        exit;
        $wxCommon = new WxCommon($this->getLog());
        $userQrFile = $wxCommon->createWxQRFile($this->getEntityManager(), 'groupOrderId=' . 1 . '&shareSourceId=' . 2, 'pages/course/index', true);
        $quanBannerFile = $this->getEntityManager()->getRepository(File::class)->find(25);
        $file = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $quanBannerFile);
        echo "qr = " . $userQrFile->getId() . "<br>";
        echo "quan = " . $quanBannerFile->getId() . "<br>";
        echo "combined=" . $file->getId();
        exit;
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);
        $referShareSource = $shareSourceRepository->find('901885c5a6bdf9e7ea');

        $user1 = $this->getEntityManager()->getRepository(User::class)->find(1);
        $user2 = $this->getEntityManager()->getRepository(User::class)->find(2);
        $user2->setParentUser($user1,$referShareSource, "草泥马妈妈妈妈澳门");
        $this->getEntityManager()->persist($user1);
        $this->getEntityManager()->flush();
        exit;
        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referUserShare
         */
        $referUserShare = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_USER]);


        /**
         * @var ProjectShareMeta $quanUserShare
         */
        $quanUserShare = $projectShareMeta->findOneBy(['metaKey' => ShareSource::QUAN_USER]);

        $shareSources = [];

        $user = $this->getEntityManager()->getRepository(User::class)->find(930);
        $page = '/pages/user/index';

        //个人信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'type' => ShareSource::REFER_USER]);
        if ($referShareSource == null) {
            $referShareSource = new ShareSource();
            $referShareSource->setType(ShareSource::REFER_USER);
            //$referShareSource->setTitle($user->getNickname() . ' ' . $referUserShare->getShareTitle());
            $referShareSource->setTitle($user->getNickname() . ' ' );
//            /**
//             * @var File $referBannerFile
//             */
//            $referBannerFile = $fileRepository->find($referUserShare->getShareBannerFileId());
//            $referShareSource->setBannerFile($referBannerFile);
            $referShareSource->setPage($page, true);
            $referShareSource->setUser($user);
            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //个人信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'type' => ShareSource::QUAN_USER]);
        if ($quanShareSource == null) {
            $quanShareSource = new ShareSource();
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'shareSourceId=' . $quanShareSource->getId(), $page, true);

            $quanShareSource->setType(ShareSource::QUAN_USER);
            /**
             * @var File $quanBannerFile
             */
            $quanBannerFile = $fileRepository->find($quanUserShare->getShareBannerFileId());
            $quanShareSource->setBannerFile(ImageGenerator::createShareQuanBannerImage($userQrFile, $quanBannerFile));
            $quanShareSource->setPage($page, true);
            $quanShareSource->setUser($user);
            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();


        var_dump($shareSources);

        exit;
        $referUserShareMeta = new ProjectShareMeta(ProjectShareMeta::REFER_USER);
        $referUserShareMeta->setShareMeta(ProjectShareMeta::$referScenes[ProjectShareMeta::REFER_USER], ShareSource::$types[ShareSource::REFER], '邀请您来参加变现课程', null, false);
        $this->getEntityManager()->persist($referUserShareMeta);

        $quanUserShareMeta = new ProjectShareMeta(ProjectShareMeta::QUAN_USER);
        $quanUserShareMeta->setShareMeta(ProjectShareMeta::$quanScenes[ProjectShareMeta::QUAN_USER], ShareSource::$types[ShareSource::QUAN], '邀请您来参加变现课程', null, true);
        $this->getEntityManager()->persist($quanUserShareMeta);

        $this->getEntityManager()->flush();

        exit;
        $service = new WxCommon($this->getLog());
        /**
         * @var FileRepository $fileRepository
         */
        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        //header('Content-Type: image/jpeg');
        echo $service->createWxQRFile($this->getEntityManager(), "userId=123&pageId=123", "pages/index/index")->getId();
        return $this->responseRaw("");
    }

    /**
     * @Route("/testStream", name="testStream", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function steamAction(Request $request) {

        $url = "https://outin-a7944acc383b11e9a86700163e1a625e.oss-cn-shanghai.aliyuncs.com/1eb988a32edb433ebfc49fdec49cf984/7de1b77d1de64334af0bc0de92a15e4f-36f439e53c29dae4a99a5b4a441cab15-ld.mp4?Expires=1551035506&OSSAccessKeyId=LTAI8bKSZ6dKjf44&Signature=ryHfgwNohaqWAPuAkMJ9fo%2FVSzs%3D";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);

        $size = 255640000;
        $length = $size;
        $start = 0;
        $end = $size - 1;
        header('Content-type: video/mp4');
        header("Accept-Ranges: 0-$length");
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }

            $c_end = ($c_end > $end) ? $end : $c_end;

            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            header('HTTP/1.1 206 Partial Content');
        }

        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: ".$length);

        $result = curl_exec($curl);

        curl_close($curl);
    }

    /**
     * @Route("/testLocalStream", name="testLocalStream", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function testLocalStream() {
        $filepath = "/Users/tandy/Downloads/coming_soon.mp4";
        // Determine file mimetype
        $fp = fopen($filepath, "rb");
        $size = filesize($filepath);
        $length = $size;
        $start = 0;
        $end = $size - 1;
        header('Content-type: video/mp4');
        header("Accept-Ranges: 0-$length");
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }

            $c_end = ($c_end > $end) ? $end : $c_end;

            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }

        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: ".$length);

        $buffer = 1024 * 8;

        while(!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            set_time_limit(0);
            echo fread($fp, $buffer);
            flush();
        }

        fclose($fp);
        exit;
    }

}
