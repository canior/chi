<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 12:04 PM
 */

namespace App\Controller\Api;

use App\Entity\CourseStudent;
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

        $thirdSession = $request->query->get('thirdSession');
        $page = $request->query->get('page', 1);
        $url = $request->query->get('url');

        $user = $this->getWxUser($thirdSession);
        $bannersArray = [];
        $productsArray = [];

        $projectBannerMetas = $projectBannerMetaRepository->findBy(['metaKey' => [ProjectBannerMeta::BANNER_HOME_1, ProjectBannerMeta::BANNER_HOME_2, ProjectBannerMeta::BANNER_HOME_3]]);
        foreach ($projectBannerMetas as $projectBannerMeta) {
            $bannersArray[] = $projectBannerMeta->getArray();
        }

        $productsQuery = $productRepository->findActiveProductsQuery();

        /**
         * @var Product[] $products
         */
        $products = $this->getPaginator()->paginate($productsQuery, $page, self::PAGE_LIMIT);

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
            'shareSources' => $this->createUserShareSource($user, $url)
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
        $course = $product->getCourse();
        $courseStudentRepository = $this->getEntityManager()->getRepository(CourseStudent::class);
        $courseStudent = $courseStudentRepository->findOneBy(['studentUser' => $user, 'course' => $course, 'status' => CourseStudent::REGISTERED]);
        $userRegistered = false;
        if ($courseStudent) {
            $userRegistered = true;
        }

        return $this->responseJson('success', 200, [
            'product' => $product->getArray(),
            'userRegistered' => $userRegistered,
            'shareSources' => $this->createProductShareSource($user, $product, $url)
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

        $productReviewsQuery = $productReviewRepository->findActiveProductReviewsQuery($productId);
        /**
         * @var ProductReview[] $productReviews
         */
        $productReviews = $this->getPaginator()->paginate($productReviewsQuery, $page, self::PAGE_LIMIT);
        $data = [];
        foreach($productReviews as $productReview) {
            $data[] = $productReview->getArray();
        }
        return $this->responseJson('success', 200, $data);
    }

}