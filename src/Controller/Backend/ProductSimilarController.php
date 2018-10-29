<?php

namespace App\Controller\Backend;

use App\Entity\ProductSimilar;
use App\Form\ProductSimilarType;
use App\Repository\ProductRepository;
use App\Repository\ProductSimilarRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProductSimilarController extends BackendController
{
    /**
     * @Route("/product/similar/", name="product_similar_index", methods="GET")
     */
    public function index(ProductSimilarRepository $productSimilarRepository, Request $request): Response
    {
        $data = [
            'title' => 'ProductSimilar 列表',
            'form' => [
                'productId' => $request->query->getInt('productId', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $productSimilarRepository->findProductSimilarsQueryBuilder($data['form']['productId']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/product_similar/index.html.twig', $data);
    }

    /**
     * @Route("/product/similar/new", name="product_similar_new", methods="GET|POST")
     */
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $productSimilar = new ProductSimilar();
        $form = $this->createForm(ProductSimilarType::class, $productSimilar);
        if ($request->query->getInt('productId')) {
            $form->get('product')->setData($productRepository->find($request->query->getInt('productId')));
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productSimilar);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('product_similar_index');
        }

        return $this->render('backend/product_similar/new.html.twig', [
            'product_similar' => $productSimilar,
            'title' => '添加 ProductSimilar',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/similar/{id}/edit", name="product_similar_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProductSimilar $productSimilar): Response
    {
        $form = $this->createForm(ProductSimilarType::class, $productSimilar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('product_similar_edit', ['id' => $productSimilar->getId()]);
        }

        return $this->render('backend/product_similar/edit.html.twig', [
            'product_similar' => $productSimilar,
            'title' => '修改 ProductSimilar',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/similar/{id}", name="product_similar_delete", methods="DELETE")
     */
    public function delete(Request $request, ProductSimilar $productSimilar): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productSimilar->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productSimilar);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('product_similar_index');
    }
}
