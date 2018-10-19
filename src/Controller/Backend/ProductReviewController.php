<?php

namespace App\Controller\Backend;

use App\Entity\ProductReview;
use App\Form\ProductReviewType;
use App\Repository\ProductReviewRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProductReviewController extends BackendController
{
    /**
     * @Route("/product/review/", name="product_review_index", methods="GET")
     */
    public function index(ProductReviewRepository $productReviewRepository, Request $request): Response
    {
        $data = [
            'title' => 'ProductReview 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $productReviewRepository->findProductReviewsQueryBuilder($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/product_review/index.html.twig', $data);
    }

    /**
     * @Route("/product/review/new", name="product_review_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $productReview = new ProductReview();
        $form = $this->createForm(ProductReviewType::class, $productReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productReview);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('product_review_index');
        }

        return $this->render('backend/product_review/new.html.twig', [
            'product_review' => $productReview,
            'title' => '添加 ProductReview',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/review/{id}/edit", name="product_review_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProductReview $productReview): Response
    {
        $form = $this->createForm(ProductReviewType::class, $productReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('product_review_edit', ['id' => $productReview->getId()]);
        }

        return $this->render('backend/product_review/edit.html.twig', [
            'product_review' => $productReview,
            'title' => '修改 ProductReview',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/review/{id}", name="product_review_delete", methods="DELETE")
     */
    public function delete(Request $request, ProductReview $productReview): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productReview->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productReview);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('product_review_index');
    }
}
