<?php

namespace App\Controller\Backend;

use App\Entity\ProductStatistics;
use App\Form\ProductStatisticsType;
use App\Repository\GroupOrderRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProductStatisticsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProductStatisticsController extends BackendController
{
    /**
     * @Route("/product/statistics/", name="product_statistics_index", methods="GET")
     */
    public function index(ProductStatisticsRepository $productStatisticsRepository, Request $request): Response
    {
        $data = [
            'title' => 'ProductStatistics 列表',
            'form' => [
                'productId' => $request->query->getInt('productId', null),
                'year' => $request->query->getInt('year', null),
                'month' => $request->query->getInt('month', null),
                'day' => $request->query->getInt('day', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'yearStart' => 2018,
            'yearEnd' => date('Y')
        ];
        $data['data'] = $productStatisticsRepository->findProductStatisticsQueryBuilder($data['form']['productId'], $data['form']['year'], $data['form']['month'], $data['form']['day']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/product_statistics/index.html.twig', $data);
    }

    /**
     * @Route("/product/statistics/info/{id}", name="product_statistics_info", methods="GET")
     */
    public function info(GroupUserOrderRepository $groupUserOrderRepository, Request $request, ProductStatistics $productStatistics, ProductStatisticsRepository $productStatisticsRepository): Response
    {
        $queryBuilder = $productStatisticsRepository->findProductStatisticsQueryBuilder($productStatistics->getProduct()->getId());
        $productStatisticsTotal = $queryBuilder->getQuery()->getOneOrNullResult();
        $data = [
            'title' => 'ProductStatistics 详情',
            'productStatistics' => $productStatistics,
            'productStatisticsTotal' => $productStatisticsTotal,
        ];
        $data['data'] = $groupUserOrderRepository->findProductGroupUserOrdersQueryBuilder($productStatistics->getProduct()->getId());
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $request->query->getInt('page', 1), self::PAGE_LIMIT);
        return $this->render('backend/product_statistics/info.html.twig', $data);
    }

    /**
     * @Route("/product/statistics/new", name="product_statistics_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $productStatistic = new ProductStatistics();
        $form = $this->createForm(ProductStatisticsType::class, $productStatistic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productStatistic);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('product_statistics_index');
        }

        return $this->render('backend/product_statistics/new.html.twig', [
            'product_statistic' => $productStatistic,
            'title' => '添加 ProductStatistics',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/statistics/{id}/edit", name="product_statistics_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProductStatistics $productStatistic): Response
    {
        $form = $this->createForm(ProductStatisticsType::class, $productStatistic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('product_statistics_edit', ['id' => $productStatistic->getId()]);
        }

        return $this->render('backend/product_statistics/edit.html.twig', [
            'product_statistic' => $productStatistic,
            'title' => '修改 ProductStatistics',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/statistics/{id}", name="product_statistics_delete", methods="DELETE")
     */
    public function delete(Request $request, ProductStatistics $productStatistic): Response
    {
        if ($this->isCsrfTokenValid('delete' . $productStatistic->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productStatistic);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('product_statistics_index');
    }
}
