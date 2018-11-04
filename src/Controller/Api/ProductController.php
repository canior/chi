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

        $products = $productRepository->findActiveProducts($request->query->getInt('page', 1), self::PAGE_LIMIT);
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
     * 获取产品详情
     *
     * @Route("/products/{id}", name="productDetail", methods="GET")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function detailAction(Request $request, Product $product): Response {
        $url = $request->query->get('url');

        return $this->responseJson('success', 200, [
            'product' => $product->getArray(),
            'shareSources' => $this->createShareSource($product, $url)
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
     * //TODO 需要确定转发配置
     * 返回转发和朋友圈的shareSource
     *
     * @param Product $product
     * @param $page
     * @return array
     */
    public function createShareSource(Product $product, $page) {

        $shareSources = [];

        $referShareSource = new ShareSource();
        $referShareSource->setType(ShareSource::REFER);
        $referShareSource->setTitle($product->getTitle());
        $referShareSource->setBannerFile($product->getMainProductImage()->getFile());
        $referShareSource->setPage($page, true);

        $quanShareSource = new ShareSource();
        $quanShareSource->setType(ShareSource::QUAN);
        $quanShareSource->setBannerFile($product->getMainProductImage()->getFile());
        $quanShareSource->setPage($page, true);

        $shareSources[] = $referShareSource->getArray();
        $shareSources[] = $quanShareSource->getArray();


        return $shareSources;
    }

}