<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-06
 * Time: 8:34 AM
 */

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Entity\Product;
use App\Repository\ProductReviewRepository;

/**
 * @Route("/wxapi")
 */
class CourseController extends ProductController
{
    /**
     * 获取课程列表
     *
     * @Route("/courses/", name="courseIndex", methods="GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return Response
     */
    public function indexAction(Request $request, ProductRepository $productRepository, ProjectBannerMetaRepository $projectBannerMetaRepository) : Response {
        return parent::indexAction($request, $productRepository, $projectBannerMetaRepository);
    }

    /**
     * @param ProductRepository $productRepository
     * @return Product[]
     */
    protected function findActiveProducts(ProductRepository $productRepository) {
        return $productRepository->findActiveProducts(true);
    }

    /**
     * 获取课程详情
     *
     * @Route("/courses/{id}", name="courseDetail", methods="GET")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function detailAction(Request $request, Product $product): Response {
        return parent::detailAction($request, $product);
    }

    /**
     * 获取指定课程的评价，评价条数限制limit
     *
     * @Route("/courses/{productId}/reviews", name="courseReviews", methods="GET")
     * @param Request $request
     * @param int $productId
     * @param ProductReviewRepository $productReviewRepository
     * @return Response
     */
    public function productReviewIndexAction(Request $request, int $productId, ProductReviewRepository $productReviewRepository): Response {
        return parent::productReviewIndexAction($request, $productId, $productReviewRepository);
    }

}