<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 12:04 PM
 */

namespace App\Controller\Api;

use App\Entity\Product;
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
     * @Route("/product/{id}", name="productDetail", methods="GET")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function detailAction(Request $request, Product $product): Response {
        return $this->responseJson('success', 200, $product);
    }


    /**
     * @Route("/product/review/{productId}", name="review", methods="GET")
     */
    public function productReviewAction(Request $request, $productId, ProductReviewRepository $productReviewRepository) {
        echo $productId;
        $productReviews = $productReviewRepository->findBy(['product' => $productId]);
        return $this->responseJson('success', 200, $productReviews);
    }
}