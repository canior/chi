<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-07
 * Time: 11:52 AM
 */

namespace App\Controller\Backend;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductReviewRepository;
use App\Entity\ProductReview;

/**
 * @Route("/backend")
 */
class OfflineCourseReviewController extends ProductReviewController
{
    /**
     * @Route("/offline/course/review/", name="offline_course_review_index", methods="GET")
     * @param ProductReviewRepository $productReviewRepository
     * @param Request $request
     * @return Response
     */
    public function index(ProductReviewRepository $productReviewRepository, Request $request): Response
    {
        return parent::index($productReviewRepository, $request);
    }

    /**
     * @param ProductReviewRepository $productReviewRepository
     * @param $rate
     * @param $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function findProductReviewsQuery(ProductReviewRepository $productReviewRepository, $rate, $status) {
        return $productReviewRepository->findProductReviewsQueryBuilder(true, false, null, $rate, $status);
    }

    /**
     * @Route("/offline/course/review/{id}/edit", name="course_review_edit", methods="GET|POST")
     * @param Request $request
     * @param ProductReview $productReview
     * @return Response
     */
    public function edit(Request $request, ProductReview $productReview): Response {
        return parent::edit($request, $productReview);
    }

    protected function getReviewType() {
        return 'course';
    }
}