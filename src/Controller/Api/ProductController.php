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
use App\Entity\ProjectTextMeta;
use App\Entity\ShareSource;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Repository\ProjectMetaRepository;
use App\Repository\ProjectTextMetaRepository;
use App\Service\Ali\AliCommon;
use App\Service\Ali\AliVod;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ProjectShareMeta;
use App\Entity\File;
use App\Service\Wx\WxCommon;
use App\Entity\User;
use App\Service\ImageGenerator;
use App\Entity\GroupUserOrder;
use App\Service\Youtube;

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

        $page = $request->query->get('page', 1);

        $bannersArray = $this->createProductBanners($projectBannerMetaRepository);
        $productsArray = [];

        $productsQuery = $this->findActiveProducts($productRepository);
        /**
         * @var Product[] $products
         */
        $products = $this->getPaginator()->paginate($productsQuery, $page, self::PAGE_LIMIT);

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
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createProductBanners(ProjectBannerMetaRepository $projectBannerMetaRepository) {
        return [];
    }

    /**
     * @param ProductRepository $productRepository
     * @return \Doctrine\ORM\Query
     */
    protected function findActiveProducts(ProductRepository $productRepository) {
        return $productRepository->findActiveProductsQuery(false);
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

        /**
         * @var GroupUserOrder $groupUserOrder
         */
        $groupUserOrder = $this->findGroupUserOrder($user, $product);

        $groupUserOrderArray = null;
        if ($groupUserOrder) {
            $groupUserOrderArray = $groupUserOrder->getArray();
        }

        /**
         * @var ProjectTextMetaRepository $projectTextMetaRepository
         */
        $projectTextMetaRepository = $this->getEntityManager()->getRepository(ProjectTextMeta::class);

        return $this->responseJson('success', 200, [
            'product' => $product->getArray(),
            'groupUserOrder' => $groupUserOrderArray,
            'shareSources' => $this->createProductShareSource($user, $product, $url),
            'textMetaArray' => $this->createProjectTextMetas($projectTextMetaRepository)
        ]);
    }

    /**
     * @param User $user
     * @param Product $product
     * @return null|GroupUserOrder
     */
    protected function findGroupUserOrder(User $user, Product $product) {
        $groupUserOrderRepository = $this->getEntityManager()->getRepository(GroupUserOrder::class);
        /**
         * @var GroupUserOrder $groupUserOrder
         */
        $groupUserOrder = $groupUserOrderRepository->findOneBy(['product' => $product, 'user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
        return $groupUserOrder;
    }

    /**
     * 获取指定产品的评价，评价条数限制limit
     *
     * @Route("/products/{productId}/reviews", name="productReviews", methods="GET")
     * @param Request $request
     * @param int $productId
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function productReviewIndexAction(Request $request, int $productId, ProductRepository $productRepository): Response {

        $this->getLog()->info("show product reviews for product " . $productId);

        $page = $request->query->get('page', 1);

        $product = $productRepository->find($productId);

        /**
         * @var ProductReview[] $productReviews
         */
        $productReviews = $this->getPaginator()->paginate($product->getActiveReviews(), $page, self::PAGE_LIMIT);

        $data = [];
        foreach($productReviews as $productReview) {
            $data[] = $productReview->getArray();
        }
        return $this->responseJson('success', 200, $data);
    }

    /**
     * @Route("/product/{productId}/aliyun/video", name="aliyunVideo", methods="GET")
     * @param ProductRepository $productRepository
     * @param $productId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \AlibabaCloud\Client\Exception\ClientException
     * @throws \AlibabaCloud\Client\Exception\ServerException
     * @return Response
     */
    public function getAliyunVideo(ProductRepository $productRepository, $productId) {
        $product = $productRepository->find($productId);

        if ($product->isAliyunVideoExpired()) {
            /*$ali = new AliCommon();
            $playInfo = $ali->getPlayInfo($product->getAliyunVideoId());

            $aliyunVideoUrl = AliVod::getVideoUrl($playInfo);
            $aliyunVideoImageUrl = AliVod::getVideoImageUrl($playInfo);
            $aliyunVideoExpiresAt = AliVod::getVideoExpiresAt($playInfo);

            $product->setAliyunVideoUrl($aliyunVideoUrl);
            $product->setAliyunVideoImageUrl($aliyunVideoImageUrl);
            $product->setAliyunVideoExpiresAt($aliyunVideoExpiresAt);
            $this->getEntityManager()->persist($product);
            $this->getEntityManager()->flush();*/

            // 根据YouTube视频id, 获取其视频文件url
            $youtube = new Youtube();
            //$aliyunVideoUrl = $youtube->getVideoUrl($product->getAliyunVideoId());
            //$aliyunVideoImageUrl = $youtube->getVideoImageUrl($product->getAliyunVideoId());
            //$aliyunVideoExpiresAt = $youtube->getExpiredAt($aliyunVideoUrl);

            //以下2行代码为临时测试
            $aliyunVideoUrl = $youtube->getVideoUrl('_7wsatiZ3Gs');
            $product->setAliyunVideoUrl($aliyunVideoUrl);
        }

        return $this->responseJson('success', 200, [
            'aliyunVideoUrl' => $product->getAliyunVideoUrl()
        ]);
    }

    /**
     * @Route("/product/{productId}/aliyun/video/image", name="aliyunVideoImage", methods="GET")
     * @param ProductRepository $productRepository
     * @param $productId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \AlibabaCloud\Client\Exception\ClientException
     * @throws \AlibabaCloud\Client\Exception\ServerException
     * @return Response
     */
    public function getAliyunVideoImage(ProductRepository $productRepository, $productId) {
        $product = $productRepository->find($productId);

        if ($product->isAliyunVideoExpired()) {
            $ali = new AliCommon();
            $playInfo = $ali->getPlayInfo($product->getAliyunVideoId());

            $aliyunVideoUrl = AliVod::getVideoUrl($playInfo);
            $aliyunVideoImageUrl = AliVod::getVideoImageUrl($playInfo);
            $aliyunVideoExpiresAt = AliVod::getVideoExpiresAt($playInfo);

            $product->setAliyunVideoUrl($aliyunVideoUrl);
            $product->setAliyunVideoImageUrl($aliyunVideoImageUrl);
            $product->setAliyunVideoExpiresAt($aliyunVideoExpiresAt);
            $this->getEntityManager()->persist($product);
            $this->getEntityManager()->flush();
        }

        return $this->responseJson('success', 200, [
            'aliyunVideoImageUrl' => $product->getAliyunVideoImageUrl()
        ]);
    }
}
