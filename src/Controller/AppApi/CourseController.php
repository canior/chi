<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/27
 * Time: 19:37
 */

namespace App\Controller\AppApi;


use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends ProductController
{
    /**
     * 首页
     * @Route("/home", name="homeIndex", methods= "GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function homeAction(Request $request, ProductRepository $productRepository, ProjectBannerMetaRepository $projectBannerMetaRepository, CategoryRepository $categoryRepository)
    {
        $bannersArray = $this->createProductBanners($projectBannerMetaRepository);

        $recommendProductsArray = $this->findHomeRecommendProducts($categoryRepository);

        $newestProductsArray = $this->findHomeNewestProducts($productRepository);

        $category = $categoryRepository->findCategoryListQuery(0, '', null)->getQuery()->getResult();
        $data = [
            'banners' => $bannersArray,
            'freeZoneBanner' => $this->createHomeFreeZoneBannerMetas($projectBannerMetaRepository),
            'recommendCategoryList' => $recommendProductsArray,
            'newestCategoryList' => $newestProductsArray,
            'category' => CommonUtil::entityArray2DataArray($category, 'simpleArray'),
        ];

        return CommonUtil::resultData($data)->toJsonResponse();
    }

    /**
     * 获取分类列表
     * @Route("/category/list", name="appGategoryList", methods= "POST")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function categoryListAction(Request $request, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'cateId', 'page', 'pageNum'
        ], ['cateId']);
        $user = $this->getAppUser();

        $parentCategory = $categoryRepository->find($requestProcess['cateId']);

        if (empty($parentCategory)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_CATEGORY_NOT_EXISTS, []);
        }
        $categoryQuery = $categoryRepository->findCategoryListQuery($requestProcess['cateId'], '', null);
        $categoryList = $this->getPaginator()->paginate($categoryQuery, $requestProcess['page'], $requestProcess['pageNum']);


        //刷新视频
        $refreshStatus = $parentCategory->refreshAliyunVideo();

        if ($refreshStatus == 2) {
            $this->entityPersist($parentCategory);
        }
        return $requestProcess->toJsonResponse([
            'categoryList' => CommonUtil::entityArray2DataArray($categoryList),
            'category' => $this->getCategoryVideoArray($parentCategory),
            'user' => CommonUtil::getInsideValue($user, 'array')
        ]);
    }

    /**
     * 获取分类详情
     * @Route("/category/detail", name="appCategoryDetail", methods= "POST")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function categoryDetailAction(Request $request, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'cateId'
        ], ['cateId']);
        $user = $this->getAppUser();

        $parentCategory = $categoryRepository->find($requestProcess['cateId']);

        if (empty($parentCategory)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_CATEGORY_NOT_EXISTS, []);
        }

        return $requestProcess->toJsonResponse([
            'category' => $this->getCategoryVideoArray($parentCategory),
            'user' => CommonUtil::getInsideValue($user, 'array')
        ]);
    }

    /**
     * 获取课程详情
     *
     * @Route("/auth/course/detail", name="appCourseDetail", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function detailAction(Request $request, ProductRepository $productRepository): JsonResponse {
        return parent::detailAction($request, $productRepository);
    }

    /**
     * 免费专区
     * @Route("/freeZone", name="appFreeZone", methods={"POST"})
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return JsonResponse
     * @author zxqc2018
     */
    public function freeZoneAction(Request $request, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request);
        return $requestProcess->toJsonResponse([
            'freeCategoryList' => $this->findHomeFreeZoneProducts($categoryRepository),
            'freeVideoInfo' => [
                'aliyunVideoUrl' => '',
                'aliyunVideoImageUrl' => '',
            ]
        ]);
    }
}