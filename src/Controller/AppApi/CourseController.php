<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/27
 * Time: 19:37
 */

namespace App\Controller\AppApi;


use App\Controller\Api\BaseController;
use App\Entity\ProjectBannerMeta;
use App\Repository\ProductRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends BaseController
{
    /**
     * 首页
     * @Route("/home", name="homeIndex", methods= "GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @author zxqc2018
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function homeAction(Request $request, ProductRepository $productRepository, ProjectBannerMetaRepository $projectBannerMetaRepository)
    {
        $bannersArray = $this->createProductBanners($projectBannerMetaRepository);

        $recommendProductsArray = $this->findHomeRecommendProducts($productRepository);

        $newestProductsArray = $this->findHomeNewestProducts($productRepository);
        $data = [
            'banners' => $bannersArray,
            'freeZoneBanner' => $this->createHomeFreeZoneBannerMetas($projectBannerMetaRepository),
            'recommendProducts' => $recommendProductsArray,
            'newestProducts' => $newestProductsArray,
            'baseUrl' => $request->getUri(),
        ];

        return CommonUtil::resultData($data)->toJsonResponse();
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
            ProjectBannerMeta::BANNER_HOME_FREE_ZONE => CommonUtil::Obj2Array($projectBannerMetaRepository->findOneBy(['metaKey' => ProjectBannerMeta::BANNER_HOME_FREE_ZONE])),
        ];
    }

    /**
     * 首页推荐课程
     * @param ProductRepository $productRepository
     * @author zxqc2018
     * @return array
     */
    protected function findHomeRecommendProducts(ProductRepository $productRepository)
    {
        return CommonUtil::entityArray2DataArray($productRepository->findRecommendProductsQueryBuilder(true, [
            'p.lookNum' => 'desc',
        ], 20)->getQuery()->getResult());
    }

    /**
     * 首页最新课程
     * @param ProductRepository $productRepository
     * @author zxqc2018
     * @return array
     */
    protected function findHomeNewestProducts(ProductRepository $productRepository)
    {
        return CommonUtil::entityArray2DataArray($productRepository->findRecommendProductsQueryBuilder(true, [
            'p.id' => 'desc',
        ], 6)->getQuery()->getResult());
    }
}