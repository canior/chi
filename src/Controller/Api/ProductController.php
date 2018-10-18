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
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class ProductController extends BaseController
{
    /**
     * @Route("/product/", name="productIndex", methods="GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function indexAction(Request $request, ProductRepository $productRepository) : Response {
        $bannersArray = [];
        $productsArray = [];
        $products = $productRepository->findActiveProducts($request->query->getInt('page', 1), self::PAGE_LIMIT);
        foreach($products as $product) {
            $productsArray[] = $product->getArray($this->getImgUrlPrefix());
        }
        $data = [
            'banners' => $bannersArray,
            'products' => $productsArray,
            'baseUrl' => $request->getUri(),
        ];
        
        return $this->responseJson('success', 200, $data);
    }

    /**
     * @Route("/product/{id}", name="productDetail", methods="GET")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function detailAction(Request $request, Product $product): Response {
        return $this->responseJson('success', 200, $product->getArray($this->getImgUrlPrefix()));
    }

    /**
     * @Route("/product/review/{id}", name="productReview", methods="GET")
     * @param Request $request
     * @param Product $product
     * @param ProductReviewRepository $productReviewRepository
     * @return Response
     */
    public function productReviewAction(Request $request, Product $product, ProductReviewRepository $productReviewRepository): Response {
        $productPreviews = $productReviewRepository->findBy(['product' => $product, 'status' => ProductReview::ACTIVE], ['id' => 'DESC'], 10);
        $data = [];
        foreach($productPreviews as $productReview) {
            $data[] = $productReview->getArray($this->getImgUrlPrefix());
        }
        return $this->responseJson('success', 200, $data);
    }

}