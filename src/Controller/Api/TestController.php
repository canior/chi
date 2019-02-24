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
     * @return Response|StreamedResponse
     */
    public function steamAction(Request $request) {

//        $response = new BinaryFileResponse('/Users/tandy/Downloads/SampleVideo_1280x720_1mb.mp4');
//        $response->setAutoEtag();
//        $response->headers->set('Content-Type', 'video/ogg');
//        // cache video for one week, not work for streaming?
//        $response->setSharedMaxAge(604800);
//        return $response;

        $file = new SplFileObject('/Users/tandy/Downloads/xuchuan1.mp4');

        $fileSize = $file->getSize();
        $response = new StreamedResponse();
        $mime = 'video';
        $fileExt = 'mp4';
        $mime = '/' . $fileExt;

        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Type', $mime);

        // Prepare File Range to read [default to the whole file content]
        $rangeMin = 0;
        $rangeMax = $fileSize - 1;
        $rangeStart = $rangeMin;
        $rangeEnd = $rangeMax;

        $httpRange = $request->server->get('HTTP_RANGE');

        // If a Range is provided, check its validity
        if ($httpRange) {
            $isRangeSatisfiable = true;

            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $httpRange, $matches)) {
                $rangeStart  = intval($matches[1]);

                if (!empty($matches[2])) {
                    $rangeEnd  = intval($matches[2]);
                }
            } else {
                // Requested HTTP-Range seems invalid.
                $isRangeSatisfiable = false;
            }

            if ($rangeStart <= $rangeEnd) {
                $length = $rangeEnd - $rangeStart + 1;
            } else {
                // Requested HTTP-Range seems invalid.
                $isRangeSatisfiable = false;
            }

            if ($file->fseek($rangeStart) !== 0) {
                // Could not seek the file to the requested range: it might be out-of-bound, or the file is corrupted?
                // Assume the range is not satisfiable.
                $isRangeSatisfiable = false;

                // NB : You might also wish to throw an Exception here...
                // Depending the server behaviour you want to set-up.
                // throw new AnyCustomFileErrorException();
            }

            if ($isRangeSatisfiable) {
                // Now the file is ready to be read...
                // Set additional headers and status code.
                // Symfony < 2.4
                // $response->setStatusCode(206);
                // Or using Symfony >= 2.4 constants
                $response->setStatusCode(StreamedResponse::HTTP_PARTIAL_CONTENT);

                $response->headers->set('Content-Range', sprintf('bytes %d/%d', $rangeStart - $rangeEnd, $fileSize));
                $response->headers->set('Content-Length', $length);
                $response->headers->set('Connection', 'Close');
            } else {
                $response = new Response();

                // Symfony < 2.4
                // $response->setStatusCode(416);
                // Or using Symfony >= 2.4 constants
                $response->setStatusCode(StreamedResponse::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
                $response->headers->set('Content-Range', sprintf('bytes */%d', $fileSize));

                return $response;
            }
        } else {
            // No range has been provided: the whole file content can be sent
            $response->headers->set('Content-Length', $fileSize);
        }

        // At this step, the request headers are ready to be sent.
        $response->prepare($request);
        $response->sendHeaders();

        // Prepare the StreamCallback
        $response->setCallback(function () use ($file, $rangeEnd) {
            $buffer = 1024 * 8;

            while (!($file->eof()) && (($offset = $file->ftell()) < $rangeEnd)) {
                set_time_limit(0);

                if ($offset + $buffer > $rangeEnd) {
                    $buffer = $rangeEnd + 1 - $offset;
                }

                echo $file->fread($buffer);
            }

            // Close the file handler
            $file = null;
        });

        // Then everything should be ready, we can send the Response content.
        $response->sendContent();
    }

}