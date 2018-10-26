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
use App\Entity\ProjectMeta;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
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
     * @Route("/products/", name="productIndex", methods="GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProjectMetaRepository $projectMetaRepository
     * @return Response
     */
    public function indexAction(Request $request, ProductRepository $productRepository, ProjectMetaRepository $projectMetaRepository) : Response {
        $bannersArray = [];
        $productsArray = [];

        $projectMetas = $projectMetaRepository->findBy(['metaKey' => [ProjectMeta::HOME_BANNER_1, ProjectMeta::HOME_BANNER_2, ProjectMeta::HOME_BANNER_3]]);
        foreach ($projectMetas as $projectMeta) {
            $bannersArray[] = $projectMeta->getMetaValue();
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
        return $this->responseJson('success', 200, $product->getArray());
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

}