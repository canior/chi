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
use App\Entity\User;
use App\Entity\UpgradeUserOrder;
use App\Entity\ShareSource;

use App\Repository\FileRepository;
use App\Repository\TeacherRepository;
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

        $user1 = $this->getEntityManager()->getRepository(User::class)->find(1);
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
//        $file = "/Users/tandy/Downloads/xuchuan1.mp4";
//        $size = filesize($file);
//        header("Content-type: video/mp4");
//        header("Accept-Ranges: bytes");
//        if(isset($_SERVER['HTTP_RANGE'])){
//            header("HTTP/1.1 206 Partial Content");
//            list($name, $range) = explode("=", $_SERVER['HTTP_RANGE']);
//            list($begin, $end) =explode("-", $range);
//            if($end == 0){
//                $end = $size - 1;
//            }
//        }else {
//            $begin = 0; $end = $size - 1;
//        }
//        header("Content-Length: " . ($end - $begin + 1));
//        header("Content-Disposition: filename=".basename($file));
//        header("Content-Range: bytes ".$begin."-".$end."/".$size);
//        $fp = fopen($file, 'rb');
//        fseek($fp, $begin);
//        while(!feof($fp)) {
//            $p = min(1024, $end - $begin + 1);
//            $begin += $p;
//            echo fread($fp, $p);
//        }
//        fclose($fp);



        $url = "https://outin-a7944acc383b11e9a86700163e1a625e.oss-cn-shanghai.aliyuncs.com/1eb988a32edb433ebfc49fdec49cf984/7de1b77d1de64334af0bc0de92a15e4f-36f439e53c29dae4a99a5b4a441cab15-ld.mp4?Expires=1551031951&OSSAccessKeyId=LTAI8bKSZ6dKjf44&Signature=AB4X%2FdCreC9pt99ihQR634UWSQE%3D";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");
        Header("Content-type: ". 'video/mp4');
        //Header("Content-Length: ".$fileSize);
        flush();

        $result = curl_exec($curl);
        curl_close($curl);


//        header("Content-Type: video/mp4");
//        echo file_get_contents("https://outin-a7944acc383b11e9a86700163e1a625e.oss-cn-shanghai.aliyuncs.com/sv/bcff41c-169202774dc/bcff41c-169202774dc.mp4?Expires=1551027219&OSSAccessKeyId=LTAI8bKSZ6dKjf44&Signature=FwJCcCUhggulPxYoAjXtwdjvbEI%3D");
//        exit;
    }

}