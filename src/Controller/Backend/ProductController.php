<?php

namespace App\Controller\Backend;

use App\Command\Product\Image\CreateOrUpdateProductImagesCommand;
use App\Command\Product\Spec\Image\CreateOrUpdateProductSpecImagesCommand;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProductController extends BackendController
{
    /**
     * @Route("/product/", name="product_index", methods="GET")
     */
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $data = [
            'title' => 'Product 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $productRepository->findProductsQueryBuilder($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/product/index.html.twig', $data);
    }

    /**
     * @Route("/product/new", name="product_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            try {
                $images = isset($request->request->get('product')['images']) ? $request->request->get('product')['images'] : [];
                $imagesCommand = new CreateOrUpdateProductImagesCommand($product->getId(), $images);
                $this->getCommandBus()->handle($imagesCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run CreateOrUpdateProductImagesCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            try {
                $specImages = isset($request->request->get('product')['specImages']) ? $request->request->get('product')['specImages'] : [];
                $specImagesCommand = new CreateOrUpdateProductSpecImagesCommand($product->getId(), $specImages);
                $this->getCommandBus()->handle($specImagesCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run CreateOrUpdateProductSpecImagesCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('backend/product/new.html.twig', [
            'product' => $product,
            'title' => '添加 Product',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}/edit", name="product_edit", methods="GET|POST")
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        // init images
        if (!$product->getProductImages()->isEmpty()) {
            $images = [];
            foreach ($product->getProductImages() as $image) {
                $images[$image->getFile()->getId()] = [
                    'id' => $image->getId(),
                    'fileId' => $image->getFile()->getId(),
                    'priority' => $image->getPriority(),
                    'name' => $image->getFile()->getName(),
                    'size' => $image->getFile()->getSize()
                ];
            }
            $form->get('images')->setData($images);
        }

        // init specImages
        if (!$product->getProductSpecImages()->isEmpty()) {
            $specImages = [];
            foreach ($product->getProductSpecImages() as $image) {
                $specImages[$image->getFile()->getId()] = [
                    'id' => $image->getId(),
                    'fileId' => $image->getFile()->getId(),
                    'priority' => $image->getPriority(),
                    'name' => $image->getFile()->getName(),
                    'size' => $image->getFile()->getSize()
                ];
            }
            $form->get('specImages')->setData($specImages);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $images = isset($request->request->get('product')['images']) ? $request->request->get('product')['images'] : [];
                $imagesCommand = new CreateOrUpdateProductImagesCommand($product->getId(), $images);
                $this->getCommandBus()->handle($imagesCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run CreateOrUpdateProductImagesCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            try {
                $specImages = isset($request->request->get('product')['specImages']) ? $request->request->get('product')['specImages'] : [];
                $specImagesCommand = new CreateOrUpdateProductSpecImagesCommand($product->getId(), $specImages);
                $this->getCommandBus()->handle($specImagesCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run CreateOrUpdateProductSpecImagesCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            $product->setUpdatedAt(time());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('backend/product/edit.html.twig', [
            'product' => $product,
            'title' => '修改 Product',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_delete", methods="DELETE")
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('product_index');
    }
}
