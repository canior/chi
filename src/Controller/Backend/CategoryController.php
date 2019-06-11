<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/17
 * Time: 14:05
 */
namespace App\Controller\Backend;

use App\Entity\Category;
use App\Entity\File;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\FileRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class CategoryController extends BackendController
{
    /**
     * @Route("/category/", name="category_index", methods="GET")
     * @param CategoryRepository $categoryRepository
     * @param Request $request
     * @return Response
     */
    public function index(CategoryRepository $categoryRepository, Request $request): Response
    {
        $data = [
            'title' => '一级分类列表',
            'form' => [
                'name' => $request->query->get('name', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $categoryRepository->findCategoryListQuery(null, $data['form']['name']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);

        return $this->render('backend/category/index.html.twig', $data);
    }

    /**
     * @Route("/category/subList/{parentId}", name="category_second_index", methods="GET")
     * @param CategoryRepository $categoryRepository
     * @param Request $request
     * @param $parentId
     * @return Response
     */
    public function secondIndex(CategoryRepository $categoryRepository, Request $request, $parentId): Response
    {
        $data = [
            'title' => '二级分类列表',
            'parentId' => $parentId,
            'form' => [
                'name' => $request->query->get('name', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $categoryRepository->findCategoryListQuery($parentId, $data['form']['name']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);

        return $this->render('backend/category/second.index.html.twig', $data);
    }

    /**
     * @Route("/category/new/{parentId?}", name="category_new", methods="GET|POST")
     * @param Request $request
     * @param $parentId
     * @param CategoryRepository $categoryRepository
     * @param FileRepository $fileRepository
     * @return Response
     */
    public function new(Request $request, $parentId, CategoryRepository $categoryRepository, FileRepository $fileRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $iconFileId = $request->request->get('category')['iconFile'] ?? null;
            $iconFile = null;
            if (!empty($iconFileId)) {
                $iconFile = $fileRepository->find($iconFileId);
            }

            if (empty($iconFile)) {
                return new Response('页面错误', 500);
            }

            if (!empty($parentId)) {
                $parentCategory = $categoryRepository->find($parentId);
                if (empty($parentCategory)) {
                    return new Response('分类不存在', 500);
                }
                $category->setParentCategory($parentCategory);
            }
            $category->setIconFile($iconFile);
            $this->entityPersist($category);

            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('category_index');
        }

        if (!empty($parentId)) {
            $renderParams = [
                'backend/category/second.new.html.twig', [
                    'category' => $category,
                    'title' => '添加二级分类',
                    'parentId' => $parentId,
                    'form' => $form->createView(),
                ]
            ];
        } else {
            $renderParams = [
                'backend/category/new.html.twig', [
                    'category' => $category,
                    'title' => '添加一级分类',
                    'form' => $form->createView(),
                ]
            ];
        }
        return call_user_func_array([$this, 'render'], $renderParams);
    }

    /**
     * @Route("/category/{id}/edit/{parentId?}", name="category_edit", methods="GET|POST")
     * @param Request $request
     * @param Category $category
     * @param FileRepository $fileRepository
     * @param $parentId
     * @return Response
     */
    public function edit(Request $request, Category $category, FileRepository $fileRepository, $parentId): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $iconFile = $category->getIconFile();
        if ($iconFile) {
            $fileArray[$iconFile->getId()] = [
                'id' => $iconFile->getId(),
                'fileId' => $iconFile->getId(),
                'priority' => 0,
                'name' => $iconFile->getName(),
                'size' => $iconFile->getSize()
            ];

            $form->get('iconFile')->setData($fileArray);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $iconFileId = $request->request->get('category')['iconFile'] ?? null;

            $iconFile = null;
            if (!empty($iconFileId)) {
                /**
                 * @var File $iconFile
                 */
                $iconFile = $fileRepository->find($iconFileId);
            }

            if (empty($iconFile)) {
                return new Response('页面错误', 500);
            }

            $category->setIconFile($iconFile);
            $this->entityPersist($category);
            $this->addFlash('notice', '修改成功');
            $routeParam = ['id' => $category->getId()];

            if (!empty($parentId)) {
                $routeParam['parentId'] = $parentId;
            }

            return $this->redirectToRoute('category_edit', $routeParam);
        }

        if (!empty($parentId)) {
            $renderParams = [
                'backend/category/second.edit.html.twig', [
                    'category' => $category,
                    'title' => '修改二级分类',
                    'parentId' => $parentId,
                    'form' => $form->createView(),
                ]
            ];
        } else {
            $renderParams = [
                'backend/category/edit.html.twig', [
                    'category' => $category,
                    'title' => '修改一级分类',
                    'form' => $form->createView(),
                ]
            ];
        }
        return call_user_func_array([$this, 'render'], $renderParams);
    }
}
