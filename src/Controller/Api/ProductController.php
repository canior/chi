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

        $products = $productRepository->findActiveProducts();
        foreach($products as $product) {
            if ($product->getCourse()) {
                if (!$product->getCourse()->isExpired()) {
                    $productsArray[] = $product->getArray();
                }
            }
        }

        $data = [
            'banners' => $bannersArray,
            'products' => $productsArray,
            'baseUrl' => $request->getUri(),
        ];
        
        return $this->responseJson('success', 200, $data);
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
        $page = $request->query->getInt('page', 1);
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
            $referShareSource = new ShareSource();
            $referShareSource->setType(ShareSource::REFER_PRODUCT);
            $referShareSource->setTitle($user->getNickname() . $referProductShare->getShareTitle() . $product->getTitle());
            $referShareSource->setUser($user);
            if ($product->getMainProductImage()) {
                $referShareSource->setBannerFile($product->getMainProductImage()->getFile());
            }
            $referShareSource->setPage($page, true);
        }


        //产品信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'product' => $product, 'type' => ShareSource::QUAN_PRODUCT]);
        if ($quanShareSource == null) {
            $quanShareSource = new ShareSource();
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'shareSourceId=' . $quanShareSource->getId(), $page, true);
            $quanShareSource->setUser($user);
            $quanShareSource->setType(ShareSource::QUAN_PRODUCT);
            if ($product->getMainProductImage() and $userQrFile) {
                $quanShareSource->setBannerFile(ImageGenerator::createShareQuanBannerImage($userQrFile, $product->getMainProductImage()->getFile()));
            }
            $quanShareSource->setPage($page, true);
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();


        return $shareSources;
    }

}