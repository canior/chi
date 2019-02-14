<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-08-27
 * Time: 6:14 PM
 */

namespace App\Controller\Api;

use App\Controller\DefaultController;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\File;
use App\Entity\ProjectShareMeta;
use App\Service\Wx\WxCommon;
use App\Entity\Product;
use App\Service\ImageGenerator;
use App\Entity\GroupOrder;

class BaseController extends DefaultController
{
    protected function response403($msg = 'no_auth', $data = [])
    {
        return $this->responseJson($msg, 403, $data);
    }

    protected function response404($msg = 'not_found', $data = [])
    {
        return $this->responseJson($msg, 404, $data);
    }

    protected function response503($msg = 'there_is_an_error', $data = [])
    {
        return $this->responseJson($msg, 503, $data);
    }

    protected function responseJson($msg = null, $code = null, $data = null)
    {
        return $this->json(compact('msg', 'code', 'data'));
    }

    protected function responseRaw($content)
    {
        return new Response($content);
    }

    protected function responseNeedLogin($msg = null)
    {
        $msg = $msg ? $msg : 'need_login';
        return $this->responseJson($msg, 403, []);
    }

    /**
     * @param $thirdSession ex. {"userId": 123, "shareSourceId": 456}
     * @return User|null
     */
    protected function getWxUser($thirdSession):?User
    {
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        return $userRepository->find($thirdSession);
    }

    protected function getImgUrlPrefix()
    {
        return $this->generateUrl('imagePreview', ['fileId' => null], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * 返回产品转发和朋友圈的shareSource
     *
     * @param User $user
     * @param Product $product
     * @param $page
     * @return array
     */
    protected function createProductShareSource(User $user, Product $product, $page) {

        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referProductShare
         */
        $referProductShare = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_PRODUCT]);

        $shareSources = [];

        //产品信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user'=> $user, 'page' => $page, 'product' => $product, 'type' => ShareSource::REFER_PRODUCT]);
        if ($referShareSource == null) {
            $referShareSource = ShareSource::factory(ShareSource::REFER_PRODUCT, $page, $user, null, $referProductShare->getShareTitle(), $product);
            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //产品信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'page' => $page, 'product' => $product, 'type' => ShareSource::QUAN_PRODUCT]);
        if ($quanShareSource == null) {
            $quanShareSource = ShareSource::factory(ShareSource::QUAN_PRODUCT, $page, $user, null, null, $product);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'shareSourceId=' . $quanShareSource->getId(), $page, true);

            $bannerFile = null;
            if ($product->getMainProductImage() and $product->getMainProductImage()->getFile()) {
                $bannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $product->getMainProductImage()->getFile());
            }
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();


        return $shareSources;
    }

    /**
     * 返回转发和朋友圈的shareSource
     *
     * @param User $user
     * @param $page
     * @return array
     */
    protected function createUserShareSource(User $user, $page) {

        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referMeta
         */
        $referMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_USER]);

        /**
         * @var ProjectShareMeta $quanMeta
         */
        $quanMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::QUAN_USER]);

        $shareSources = [];

        //个人信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'page' => $page, 'type' => ShareSource::REFER_USER]);
        if ($referShareSource == null) {

            $referBannerFile = null;
            if ($referMeta->getShareBannerFileId()) {
                /**
                 * @var File $referBannerFile
                 */
                $referBannerFile = $fileRepository->find($referMeta->getShareBannerFileId());
            }
            $referShareSource = ShareSource::factory(ShareSource::REFER_USER, $page, $user, $referBannerFile, $referMeta->getShareTitle());

            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //个人信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'page' => $page, 'type' => ShareSource::QUAN_USER]);
        if ($quanShareSource == null) {

            $quanShareSource = ShareSource::factory(ShareSource::QUAN_USER, $page, $user);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'shareSourceId=' . $quanShareSource->getId(), $page, true);

            $quanBannerFile = null;
            if ($quanMeta->getShareBannerFileId()) {
                /**
                 * @var File $quanBannerFile
                 */
                $quanBannerFile = $fileRepository->find($quanMeta->getShareBannerFileId());
            }

            $bannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $quanBannerFile);
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();

        return $shareSources;
    }

    /**
     *
     * 返回转发和朋友圈的shareSource
     *
     * @param GroupOrder $groupOrder
     * @param $page
     * @return array
     */
    protected function createGroupOrderShareSource(GroupOrder $groupOrder, $page) {

        $user = $groupOrder->getUser();
        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referMeta
         */
        $referMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_GROUP_ORDER]);

        /**
         * @var ProjectShareMeta $quanMeta
         */
        $quanMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::QUAN_GROUP_ORDER]);

        $shareSources = [];

        //拼团页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['groupOrder' => $groupOrder, 'page' => $page, 'type' => ShareSource::REFER_GROUP_ORDER]);
        if ($referShareSource == null) {

            $referBannerFile = null;
            if ($referMeta->getShareBannerFileId()) {
                /**
                 * @var File $referBannerFile
                 */
                $referBannerFile = $fileRepository->find($referMeta->getShareBannerFileId());
            }
            $referShareSource = ShareSource::factory(ShareSource::REFER_GROUP_ORDER, $page, $user, $referBannerFile, $referMeta->getShareTitle(), $groupOrder->getProduct(), $groupOrder);

            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //拼团页面朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['groupOrder' => $groupOrder, 'page' => $page, 'type' => ShareSource::QUAN_GROUP_ORDER]);
        if ($quanShareSource == null) {

            $quanShareSource = ShareSource::factory(ShareSource::QUAN_GROUP_ORDER, $page, $user);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'groupOrderId=' . $groupOrder->getId() . '&shareSourceId=' . $quanShareSource->getId(), $page, true);

            $quanBannerFile = null;
            if ($quanMeta->getShareBannerFileId()) {
                /**
                 * @var File $quanBannerFile
                 */
                $quanBannerFile = $fileRepository->find($quanMeta->getShareBannerFileId());
            }

            $bannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $quanBannerFile);
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();

        return $shareSources;
    }
}