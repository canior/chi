<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 15:32
 */

namespace App\Controller\GongZhong;

use App\Repository\ProductRepository;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OfflineCourseController
 * @package App\Controller\Gongzhong
 * @author zxqc2018
 */
class OfflineCourseController extends GongZhongBaseController
{
    /**
     * 获取线下课程列表
     *
     * @Route("/offlineCourses", name="gzhOfflineCourseIndex", methods={"POST","OPTIONS"})
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

        $courseCountQuery = $productRepository->findAppProductsQueryBuilder(true, false, [
            'offlineCourseType' => $requestProcess['offlineCourseType'],
            'isGetCount' => true
        ]);
        return $requestProcess->toJsonResponse([
            'courseList' => CommonUtil::entityArray2DataArray($courseList),
            'total' => CommonUtil::getTotalQueryCount($courseCountQuery),
            'user' => CommonUtil::getInsideValue($user, 'array')
        ]);
    }

    /**
     * 获取课程详情
     *
     * @Route("/offlineCourse/detail", name="gzhOfflineCourseDetail", methods="POST")
     * @return JsonResponse
     */
    public function detailAction(): JsonResponse {
        $requestProcess = $this->processRequest(null, [
            'url', 'productId', 'page', 'pageNum','shareSourceId'
        ], ['productId']);
        return FactoryUtil::offlineCourseService()->getDetailInfo($requestProcess, $this->getAppUser(true), 'gzh')->toJsonResponse();
    }
}