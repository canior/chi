<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 12:04 PM
 */

namespace App\Controller\Api;

use App\Entity\Product;
use App\Entity\ProductReview;
use App\Entity\ProjectBannerMeta;
use App\Entity\ProjectMeta;
use App\Entity\ShareSource;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Repository\ProjectMetaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ProjectShareMeta;
use App\Entity\File;
use App\Service\Wx\WxCommon;
use App\Entity\User;
use App\Service\ImageGenerator;

/**
 * @Route("/wxapi")
 */
class ProductController extends BaseController
{
    /**
     * 获取产品列表
     *
     * @Route("/products/introduction", name="productIntroduction", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function introductionAction(Request $request) {
        $data = [];
        return $this->render('api/introduction.html.twig', $data);
    }


    /**
     * 获取产品列表
     *
     * @Route("/products/", name="productIndex", methods="GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return Response
     */
    public function indexAction(Request $request, ProductRepository $productRepository, ProjectBannerMetaRepository $projectBannerMetaRepository) : Response {
        $bannersArray = [];
        $productsArray = [];

        $projectBannerMetas = $projectBannerMetaRepository->findBy(['metaKey' => [ProjectBannerMeta::BANNER_HOME_1, ProjectBannerMeta::BANNER_HOME_2, ProjectBannerMeta::BANNER_HOME_3]]);
        foreach ($projectBannerMetas as $projectBannerMeta) {
            $bannersArray[] = $projectBannerMeta->getArray();
        }

        $products = $this->findActiveProducts($productRepository);
        foreach($products as $product) {
            $productsArray[] = $product->getArray();
        }

        $data = [
            'banners' => $bannersArray,
            'products' => $productsArray,
            'baseUrl' => $request->getUri(),
        ];
        
        return $this->responseJson('success', 200, $data);
    }

    /**
     * @param ProductRepository $productRepository
     * @return Product[]
     */
    protected function findActiveProducts(ProductRepository $productRepository) {
        return $productRepository->findActiveProducts();
    }

    /**
     * 获取产品详情
     *
     * @Route("/products/{id}", name="productDetail", methods="GET")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function detailAction(Request $request, Product $product): Response {
        $thirdSession = $request->query->get('thirdSession');
        $url = $request->query->get('url');

        $user = $this->getWxUser($thirdSession);

        return $this->responseJson('success', 200, [
            'product' => $product->getArray(),
            'shareSources' => $this->createShareSource($user, $product, $url)
        ]);
    }

    /**
     * 获取指定产品的评价，评价条数限制limit
     *
     * @Route("/products/{productId}/reviews", name="productReviews", methods="GET")
     * @param Request $request
     * @param int $productId
     * @param ProductReviewRepository $productReviewRepository
     * @return Response
     */
    public function productReviewIndexAction(Request $request, int $productId, ProductReviewRepository $productReviewRepository): Response {
        $page = $request->query->get('page', 1);
        $productReviews = $productReviewRepository->findActiveProductReviews($productId, $page, 5);
        $data = [];
        foreach($productReviews as $productReview) {
            $data[] = $productReview->getArray();
        }
        return $this->responseJson('success', 200, $data);
    }

    /**
     * 返回转发和朋友圈的shareSource
     *
     * @param User $user
     * @param Product $product
     * @param $page
     * @return array
     */
    private function createShareSource(User $user, Product $product, $page) {
        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referProductShare
         */
        $referProductShare = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_PRODUCT]);

        $shareSources = [];

        //产品信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user'=> $user, 'product' => $product, 'type' => ShareSource::REFER_PRODUCT]);
        if ($referShareSource == null) {
            $referShareSource = ShareSource::factory(ShareSource::REFER_PRODUCT, $page, $user, null, $referProductShare->getShareTitle(), $product);
            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //产品信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'product' => $product, 'type' => ShareSource::QUAN_PRODUCT]);
        if ($quanShareSource == null) {
            $quanShareSource = ShareSource::factory(ShareSource::QUAN_PRODUCT, $page, $user, null, null, $product);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'shareSourceId=' . $quanShareSource->getId(), $page, true);

            $bannerFile = null;
            if ($product->getMainProductImage() and $product->getMainProductImage()->getFile()) {
                $bannerFile = ImageGenerator::createShareQuanBannerImage($userQrFile, $product->getMainProductImage()->getFile());
            }
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();


        return $shareSources;
    }

}