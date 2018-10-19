<?php

namespace App\Controller\Backend;

use App\Entity\Product;
use App\Entity\ProductReview;
use App\Form\ProductReviewType;
use App\Repository\ProductRepository;
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
            'title' => '评价产品列表',
            'form' => [
                'productId' => $request->query->get('productId', null),
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $productReviewRepository->findReviewedProductsQueryBuilder($data['form']['productId'], $data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/product_review/index.html.twig', $data);
    }

    /**
     * @Route("/product/review/info/{id}", name="product_review_info", methods="GET")
     */
    public function info(ProductReviewRepository $productReviewRepository, Request $request, Product $product): Response
    {
        $data = [
            'title' => '产品评价详情',
            'product' => $product,
            'page' => $request->query->getInt('page', 1)
        ];
        $data['statistics'] = $productReviewRepository->findProductReviewStatistics($product->getId());
        $data['data'] = $productReviewRepository->findProductReviewsQueryBuilder($product->getId());
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['page'], self::PAGE_LIMIT);
        return $this->render('backend/product_review/info.html.twig', $data);
    }

    /**
     * @Route("/product/review/new", name="product_review_new", methods="GET|POST")
     */
    public function new(ProductRepository $productRepository, Request $request): Response
    {
        $productReview = new ProductReview();
        $form = $this->createForm(ProductReviewType::class, $productReview);

        if ($request->query->get('productId')) {
            $product = $productRepository->find($request->query->get('productId'));
            $form->get('product')->setData($product);
        }

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
        if ($this->isCsrfTokenValid('delete' . $productReview->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productReview);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('product_review_index');
    }
}
