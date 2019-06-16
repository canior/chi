<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/16
 * Time: 15:27
 */

namespace App\Controller\AppApi;

use App\Repository\ProductRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OfflineCourseController
 * @package App\Controller\AppApi
 * @author zxqc2018
 */
class OfflineCourseController extends CourseController
{
    /**
     * 获取线下课程列表
     *
     * @Route("/offlineCourses", name="appOfflineCourseIndex", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function indexAction(Request $request, ProductRepository $productRepository) : JsonResponse
    {
        $requestProcess = $this->processRequest($request, [
            'offlineCourseType', 'page', 'pageNum'
        ], ['offlineCourseType']);
        $user = $this->getAppUser();

        $courseQuery = $productRepository->findAppProductsQueryBuilder(true, false, [
            'offlineCourseType' => $requestProcess['offlineCourseType']
        ]);
        $courseList = $this->getPaginator()->paginate($courseQuery, $requestProcess['page'], $requestProcess['pageNum']);

        return $requestProcess->toJsonResponse([
            'courseList' => CommonUtil::entityArray2DataArray($courseList),
            'user' => CommonUtil::getInsideValue($user, 'array')
        ]);
    }

    /**
     * 获取课程详情
     *
     * @Route("/auth/offlineCourse/detail", name="appOfflineCourseDetail", methods="POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function detailAction(Request $request, ProductRepository $productRepository): JsonResponse {
        return parent::detailAction($request, $productRepository);
    }
}