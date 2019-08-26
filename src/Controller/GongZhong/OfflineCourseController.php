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
use App\Repository\GroupUserOrderRepository;
use App\Repository\CourseStudentRepository;

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

        $data  = [];
        foreach ($courseList as $k => $v) {
            $item = $v->getArray();
            $item['is_initiator'] = false;
            if( $user && $v->getCourse()->getInitiator() && $v->getCourse()->getInitiator()->getId() ==  $user->getId() ){
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
     * @Route("/offlineCourse/detail", name="gzhOfflineCourseDetail", methods="POST")
     * @return JsonResponse
     */
    public function detailAction(): JsonResponse {
        $requestProcess = $this->processRequest(null, [
            'url', 'productId', 'page', 'pageNum','shareSourceId'
        ], ['productId']);
        return FactoryUtil::offlineCourseService()->getDetailInfo($requestProcess, $this->getAppUser(true), 'gzh')->toJsonResponse();
    }

    /**
     * 
     *
     * @Route("/offlineCourse/detail/user", name="gzhOfflineCourseDetailUser", methods="POST")
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
     * @Route("/offlineCourse/sign/user", name="gzhCourseSignUser", methods="POST")
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