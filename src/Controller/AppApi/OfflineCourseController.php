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
use App\Repository\GroupUserOrderRepository;
use App\Service\Util\FactoryUtil;
use App\Repository\CourseStudentRepository;

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

        $courseCountQuery = $productRepository->findAppProductsQueryBuilder(true, false, [
            'offlineCourseType' => $requestProcess['offlineCourseType'],
            'isGetCount' => true
        ]);

        $data  = [];
        foreach ($courseList as $k => $v) {
            $item = $v->getArray();
            $item['is_initiator'] = false;
            if( $user && $v->getCourse()->getInitiator() && $v->getCourse()->getInitiator()->getId() ==  $user->getID() ){
                $item['is_initiator'] = true;
            }
            $data[] = $item;
        }

        return $requestProcess->toJsonResponse([
            'courseList' => $data,
            'total' => CommonUtil::getTotalQueryCount($courseCountQuery),
            'user' => CommonUtil::obj2Array($user),
        ]);
    }

    /**
     * 获取课程详情
     *
     * @Route("/auth/offlineCourse/detail", name="appOfflineCourseDetail", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function detailAction(Request $request): JsonResponse {
        return parent::detailAction($request);
    }

    /**
     * 
     *
     * @Route("/offlineCourse/detail/user", name="appOfflineCourseDetailUser", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function courseUserAction(Request $request,GroupUserOrderRepository $groupUserOrderRepository): JsonResponse {

        $requestProcess = $this->processRequest($request, [
            'productId', 'page', 'pageNum','paymentStatus'
        ], ['productId']);
        $user = $this->getAppUser();

        $page  = isset($requestProcess['page'])?$requestProcess['page']:1;
        $pageNum  = isset($requestProcess['pageNum'])?$requestProcess['pageNum']:20;
        $paymentStatus = isset($requestProcess['paymentStatus'])?$requestProcess['paymentStatus']:'';

        $groupUserOrderQuery = $groupUserOrderRepository->groupUserOrdersQueryBuilder($requestProcess['productId'],$paymentStatus);
        $groupUserOrder = $this->getPaginator()->paginate($groupUserOrderQuery,$page, $pageNum);

        $groupUserCountQuery = $groupUserOrderRepository->groupUserOrdersQueryBuilder($requestProcess['productId'],$paymentStatus,true);

        return CommonUtil::resultData(
            [
                'groupUserOrder'=>CommonUtil::entityArray2DataArray($groupUserOrder),
                'total' => CommonUtil::getTotalQueryCount($groupUserCountQuery),
            ]
        )->toJsonResponse();
    }

    /**
     * 
     *
     * @Route("/offlineCourse/sign/user", name="appCourseSignUser", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function courseSignUserAction(Request $request, ProductRepository $productRepository,CourseStudentRepository  $courseStudentRepository): JsonResponse {

        $requestProcess = $this->processRequest($request, [
            'productId', 'page', 'pageNum'
        ], ['productId']);
        $user = $this->getAppUser();

        $page  = isset($requestProcess['page'])?$requestProcess['page']:1;
        $pageNum  = isset($requestProcess['pageNum'])?$requestProcess['pageNum']:20;
        $productId  = isset($requestProcess['productId'])?$requestProcess['productId']:null;

        $product = $productRepository->find($productId);
        $courseId = $product->getCourse()->getId();

        $courseStudentQuery = $courseStudentRepository->courseStudentsQueryBuilder($courseId);
        $courseStudent = $this->getPaginator()->paginate($courseStudentQuery,$page, $pageNum);
        $courseStudentCountQuery = $courseStudentRepository->courseStudentsQueryBuilder($courseId,true);

        return CommonUtil::resultData(
            [
                'courseStudent'=>CommonUtil::entityArray2DataArray($courseStudent),
                'total' => CommonUtil::getTotalQueryCount($courseStudentCountQuery),
            ]
        )->toJsonResponse();
    }
}