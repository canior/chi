<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/13
 * Time: 10:48
 */

namespace App\Controller\AppApi;


use App\Entity\Course;
use App\Entity\Follow;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\ProjectBannerMeta;
use App\Entity\ProjectTextMeta;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Repository\ProjectTextMetaRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProductController
 * @package App\Controller\AppApi
 * @author zxqc2018
 */
class ProductController extends AppApiBaseController
{
    /**
     * 获取产品详情
     * @Route("/auth/product/detail", name="appProductDetail", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function detailAction(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $requestProcess = $this->processRequest($request, [
            'url', 'productId'
        ], ['productId']);

        $user = $this->getAppUser();
        $productId = $requestProcess['productId'];
        $url = $requestProcess['url'];

        $product = $productRepository->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        $shareSources = $this->createProductShareSource($user, $product, $url);

        /**
         * @var ProjectTextMetaRepository $projectTextMetaRepository
         */
        $projectTextMetaRepository = $this->getEntityManager()->getRepository(ProjectTextMeta::class);

        $data = [
            'product' => $product->getArray(),
            'shareSources' => $shareSources,
            'textMetaArray' => $this->createProjectTextMetas($projectTextMetaRepository)
        ];


        //课程加上对应的权限以及
        if ($product->isCourseProduct()) {
            if ($product->getCourse()->isOnline()) {
                $data['product']['isPermission'] = $product->getCourse()->isPermission($user);
                $data['product']['callStatus'] = '';
                if (empty($data['product']['isPermission'])) {
                    $newGroupOrder = $user->getNewestGroupUserOrder($product, true);
                    $data['product']['callStatus'] = CommonUtil::getInsideValue($newGroupOrder, 'getStatus', '');
                }
                $data['product']['isFollow'] = !empty($this->followCourseInfo($user, $product->getCourse()));
            } else {
                /**
                 * @var GroupUserOrder $groupUserOrder
                 */
                $groupUserOrder = $this->findGroupUserOrder($user, $product);
                $data['groupUserOrder'] = CommonUtil::getInsideValue($groupUserOrder);
            }
        }
        return $requestProcess->toJsonResponse($data);
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
     * 首页轮播banner
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createProductBanners(ProjectBannerMetaRepository $projectBannerMetaRepository)
    {
        $bannersArray = $this->createHomePageProjectBannerMetas($projectBannerMetaRepository);
        return $bannersArray;
    }

    /**
     * 首页免费专区banner
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createHomeFreeZoneBannerMetas(ProjectBannerMetaRepository $projectBannerMetaRepository) {
        return [
            ProjectBannerMeta::BANNER_HOME_FREE_ZONE => CommonUtil::obj2Array($projectBannerMetaRepository->findOneBy(['metaKey' => ProjectBannerMeta::BANNER_HOME_FREE_ZONE])),
        ];
    }

    /**
     * 首页推荐课程
     * @param CategoryRepository $categoryRepository
     * @return array
     * @author zxqc2018
     */
    protected function findHomeRecommendProducts(CategoryRepository $categoryRepository)
    {
        return CommonUtil::entityArray2DataArray($categoryRepository->findRecommendCategory()->getQuery()->getResult());
    }


    /**
     * 首页免费专区课程
     * @param CategoryRepository $categoryRepository
     * @return array
     * @author zxqc2018
     */
    protected function findHomeFreeZoneProducts(CategoryRepository $categoryRepository)
    {
        return CommonUtil::entityArray2DataArray($categoryRepository->findFreeCategory()->getQuery()->getResult());
    }

    /**
     * 首页最新课程
     * @param ProductRepository $productRepository
     * @author zxqc2018
     * @return array
     */
    protected function findHomeNewestProducts(ProductRepository $productRepository)
    {
        return array_chunk(CommonUtil::entityArray2DataArray($productRepository->findAppProductsQueryBuilder(true, null, [
            'orderBy' => ['p.id' => 'desc'], 'limit' => 6
        ])->getQuery()->getResult()), 2);
    }

    /**
     * 取得关注课程信息
     * @param User $user
     * @param Course $course
     * @return Follow
     * @author zxqc2018
     */
    protected function followCourseInfo(User $user, Course $course)
    {
        $followRepository = $this->getEntityManager()->getRepository(Follow::class);
        /**
         * @var Follow $follow
         */
        $follow = $followRepository->findOneBy(['dataId' => $course->getId(), 'user' => $user, 'type' => Follow::COURSE]);
        return $follow;
    }
}