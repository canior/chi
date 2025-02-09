<?php

namespace App\Controller\Backend;

use App\Command\Product\Review\CreateOrUpdateProductReviewImagesCommand;
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
            'title' => '产品评价',
            'form' => [
                'productId' => $request->query->getInt('productId', null),
                'rate' => $request->query->getInt('rate', null),
                'status' => $request->query->get('status', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'statuses' => ProductReview::$statuses
        ];
        $data['data'] = $productReviewRepository->findProductReviewsQueryBuilder($data['form']['productId'], $data['form']['rate'], $data['form']['status']);
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
        $form->get('status')->setData(array_search($productReview->getStatusText(), ProductReview::$statuses));

        if ($request->query->get('productId')) {
            $product = $productRepository->find($request->query->get('productId'));
            $form->get('product')->setData($product);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('product_review')['status'];
            $isMethod = 'is' . ucwords($status);
            if (in_array($status, array_keys(Product::$statuses)) && !$productReview->$isMethod()) {
                $setMethod = 'set' . ucwords($status);
                $productReview->$setMethod();
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($productReview);
            $em->flush();

            try {
                $images = isset($request->request->get('product_review')['images']) ? $request->request->get('product_review')['images'] : [];
                $imagesCommand = new CreateOrUpdateProductReviewImagesCommand($productReview->getId(), $images);
                $this->getCommandBus()->handle($imagesCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run CreateOrUpdateProductReviewImagesCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('product_edit', ['id' => $productReview->getProduct()->getId()]);
        }

        return $this->render('backend/product_review/new.html.twig', [
            'product_review' => $productReview,
            'title' => '添加产品评价',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/review/{id}/edit", name="product_review_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProductReview $productReview): Response
    {
        $form = $this->createForm(ProductReviewType::class, $productReview);
        $form->get('status')->setData(array_search($productReview->getStatusText(), ProductReview::$statuses));

        // init images
        if (!$productReview->getProductReviewImages()->isEmpty()) {
            $images = [];
            foreach ($productReview->getProductReviewImages() as $image) {
                $images[$image->getImageFile()->getId()] = [
                    'id' => $image->getId(),
                    'fileId' => $image->getImageFile()->getId(),
                    'name' => $image->getImageFile()->getName(),
                    'size' => $image->getImageFile()->getSize()
                ];
            }
            $form->get('images')->setData($images);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('product_review')['status'];
            $isMethod = 'is' . ucwords($status);
            if (in_array($status, array_keys(Product::$statuses)) && !$productReview->$isMethod()) {
                $setMethod = 'set' . ucwords($status);
                $productReview->$setMethod();
            }

            try {
                $images = isset($request->request->get('product_review')['images']) ? $request->request->get('product_review')['images'] : [];
                $imagesCommand = new CreateOrUpdateProductReviewImagesCommand($productReview->getId(), $images);
                $this->getCommandBus()->handle($imagesCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run CreateOrUpdateProductReviewImagesCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            $productReview->setUpdatedAt(time());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');

            if ($request->query->getInt('productId')) {
                return $this->redirectToRoute('product_edit', ['id' => $request->query->getInt('productId')]);
            }
            return $this->redirectToRoute('product_review_edit', array_merge(['id' => $productReview->getId()], $request->query->all()));
        }

        return $this->render('backend/product_review/edit.html.twig', [
            'product_review' => $productReview,
            'title' => '修改产品评价',
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
