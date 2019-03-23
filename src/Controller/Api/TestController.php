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
use App\Entity\Product;

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
        //test code login

        $courseRepository = $this->getEntityManager()->getRepository(Course::class);
        $course = $courseRepository->find(1);
        $user = null;//$this->getEntityManager()->getRepository(User::class)->find(1);
        echo $course->isWelcomed($user) ? 'true' : 'false';

        exit;
        /**
         * @var User $user
         */
        $user = $this->getEntityManager()->getRepository(User::class)->find(2);
        $page = "pages/index/index";
        /**
         * @var Product $product
         */
        $product = $this->getEntityManager()->getRepository(Product::class)->find(10);

        //$shareSources = $this->createUserShareSource($user, $page);
        $shareSources = $this->createProductShareSource($user, $product, $page);
        var_dump($shareSources);




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

}
