<?php

namespace App\Controller\Backend;

use App\Command\Product\Image\CreateOrUpdateProductImagesCommand;
use App\Command\Product\Spec\Image\CreateOrUpdateProductSpecImagesCommand;
use App\Command\Product\UpdateProductRewardsCommand;
use App\Entity\Product;
use App\Entity\ProjectRewardsMeta;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\ProjectRewardsMetaRepository;
use Endroid\QrCode\Factory\QrCodeFactory;
use Knp\Bundle\SnappyBundle\Snappy\Response\JpegResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use Intervention\Image\AbstractFont;
use Intervention\Image\ImageManagerStatic as Image;

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
            'title' => '产品管理',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'status' => $request->query->get('status', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'statuses' => Product::$statuses
        ];
        $data['data'] = $productRepository->findProductsQueryBuilder($data['form']['keyword'], $data['form']['status']);
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
        $form->get('status')->setData(array_search($product->getStatusText(), Product::$statuses));
        $form->get('hasCoupon')->setData(array_search($product->isHasCoupon(), Product::$hasCouponValues));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('product')['status'];
            $hasCoupon = $request->request->get('product')['hasCoupon'];
            $isMethod = 'is' . ucwords($status);
            if (in_array($status, array_keys(Product::$statuses)) && !$product->$isMethod()) {
                $setMethod = 'set' . ucwords($status);
                $product->$setMethod();
            }
            $product->setHasCoupon($hasCoupon);

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

            try {
                $productRewardsCommand = new UpdateProductRewardsCommand($product->getId());
                $this->getCommandBus()->handle($productRewardsCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run UpdateProductRewardsCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            //add share image
            $shareImageFileId = isset($request->request->get('product')['shareImage']) ? $request->request->get('product')['shareImageFile'] : null;
            if ($shareImageFileId) {
                /**
                 * @var File $shareImageFile
                 */
                $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
                $product->setShareImageFile($shareImageFile);
                $this->getEntityManager()->persist($product);
            }

            $this->getEntityManager()->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('backend/product/new.html.twig', [
            'product' => $product,
            'title' => '添加产品',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}/edit", name="product_edit", methods="GET|POST")
     */
    public function edit(Request $request, Product $product, ProductReviewRepository $productReviewRepository, ProjectRewardsMetaRepository $projectRewardsMetaRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->get('status')->setData(array_search($product->getStatusText(), Product::$statuses));
        $form->get('hasCoupon')->setData(array_search($product->isHasCoupon(), Product::$hasCouponValues));
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

        $shareImageFile = $product->getShareImageFile();
        if ($shareImageFile) {
            $fileArray[$shareImageFile->getId()] = [
                'id' => $shareImageFile->getId(),
                'fileId' => $shareImageFile->getId(),
                'priority' => 0,
                'name' => $shareImageFile->getName(),
                'size' => $shareImageFile->getSize()
            ];
            $form->get('shareImageFile')->setData($fileArray);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('product')['status'];
            $isMethod = 'is' . ucwords($status);
            if (in_array($status, array_keys(Product::$statuses)) && !$product->$isMethod()) {
                $setMethod = 'set' . ucwords($status);
                $product->$setMethod();
            }

            $hasCoupon = $request->request->get('product')['hasCoupon'];
            $product->setHasCoupon($hasCoupon);

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

            try {
                $productRewardsCommand = new UpdateProductRewardsCommand($product->getId());
                $this->getCommandBus()->handle($productRewardsCommand);
            } catch (\Exception $e) {
                $this->getLog()->error('can not run UpdateProductRewardsCommand because of' . $e->getMessage());
                if ($this->isDev()) {
                    dump($e->getFile());
                    dump($e->getMessage());
                    die;
                }
                return new Response('页面错误', 500);
            }

            //update share image
            $shareImageFileId = isset($request->request->get('product')['shareImageFile']) ? $request->request->get('product')['shareImageFile'] : [];
            if ($shareImageFileId) {
                /**
                 * @var File $shareImageFile
                 */
                $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
                $product->setShareImageFile($shareImageFile);

            } else {
                $product->setShareImageFile(null);
            }

            $product->setUpdatedAt(time());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        /**
         * @var ProjectRewardsMeta $projectRewardsMeta
         */
        $projectRewardsMeta = $projectRewardsMetaRepository->findOneBy(['metaKey' => ProjectRewardsMeta::PROJECT_REWARDS]);

        return $this->render('backend/product/edit.html.twig', [
            'product' => $product,
            'statistics' => $productReviewRepository->findProductReviewStatistics($product->getId()),
            'projectRewardsMeta' => $projectRewardsMeta,
            'title' => '修改产品',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_delete", methods="DELETE")
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/product/intervention/{id}", name="product_intervention", methods="GET")
     */
    public function intervention(Product $product, QrCodeFactory $qrCodeFactory)
    {
//        Image::configure(['driver' => 'imagick']);
        $image = Image::make($product->getMainProductImage()->getFile()->getAbsolutePath())
            ->resize(800, 800)
            ->text('Price: $' . $product->getPrice(), 400, 400, function (AbstractFont $font) {
                // 指定字体文件
//                $font->file('/System/Library/Fonts/STHeiti Light.ttc');
                $font->file(5);
                $font->size(64);
                $font->color('#0EBEAE');
                $font->align('center');
                $font->valign('top');
//                $font->angle(10);
            });

        $qrCode = $qrCodeFactory->create('Test code');
//        return new Response($qrCode->writeString(), Response::HTTP_OK, ['Content-Type' => $qrCode->getContentType()]);

        $image->insert($qrCode->writeString(), 'bottom', 0, 50);

        return new Response($image->response('png'), Response::HTTP_OK, ['Content-Disposition' => 'inline; filename="' . $product->getMainProductImage()->getFile()->getName() . '"', 'Content-Type' => 'image/png']);

        /*
        $response = new Response();
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $product->getMainProductImage()->getFile()->getName());
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/png');
        $response->setContent($image->response('png'));
        return $response;
        */
    }

    /**
     * @Route("/product/snappy/{id}", name="product_snappy", methods="GET")
     */
    public function snappy(Product $product, QrCodeFactory $qrCodeFactory)
    {
        $data = [
            'realWebPath' => realpath($this->container->getParameter('kernel.root_dir') . '/../'),
            'product' => $product,
            'qrCodeUri' => $qrCodeFactory->create('Test code')->writeDataUri()
        ];
        $html = $this->renderView('tests/snappy.html.twig', $data);

        return new JpegResponse(
            $this->get('knp_snappy.image')->getOutputFromHtml($html),
            'snappy.jpg'
        );
    }
}
