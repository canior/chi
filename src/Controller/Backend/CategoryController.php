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
use App\Service\Util\CommonUtil;

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
        // NG
        $data = [];
        if( $request->query->get('isNg') ){

            $data = [
                'title' => '一级分类列表',
                'form' => [
                    'name' => $request->query->get('name', null),
                    'page' => $request->query->getInt('page', 1)
                ]
            ];
            $data['data'] = $categoryRepository->findCategoryListQuery(null, $data['form']['name']);
            $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);


            $datas  = [];
            foreach ($data['pagination'] as $k => $v) {
                $datas[] = $v->getLittleArray();
            }
            return CommonUtil::resultData($datas)->toJsonResponse();
        }

        return $this->render('backend/category/index.html.twig', $data);
    }

    /**
     * 菜单树
     *
     * @return \Illuminate\Http\Response
     */
    public function getTempTree($data, $pId = 0)
    {
        $tree = array();
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pId)
            {
                //子
                $item = $this->getTempTree($data, $v['id']);
                $v['isLeaf'] = true;
                if(count($item) ){
                    $v['isLeaf'] = false;
                    $v['children'] = $item ;
                }

                $v['key'] = $v['id'];
                $v['title'] = $v['name'];
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * @Route("/category/create", name="categoryCreate", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request,CategoryRepository $categoryRepository): Response{

        $id = $request->get('id', null);
        if( $id ){
            $data['category'] = $categoryRepository->find($id);
        }

        $data['categorys'] = $this->getTempTree( $categoryRepository->getCategoryList() );

        return CommonUtil::resultData($data)->toJsonResponse();

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
    public function new(Request $request, $parentId, CategoryRepository $categoryRepository): Response
    {

        $datas = json_decode($request->getContent(), true);
        $title = isset($datas['title']) ? $datas['title'] : null;
        $status = isset($datas['status']) ? $datas['status'] : 'active';
        $priority = isset($datas['priority']) ? $datas['priority'] : null;
        $parent_id = isset($datas['parent_id']) ? $datas['parent_id'] : null;
        // $courseShowType = isset($datas['courseShowType']) ? $datas['courseShowType'] : null;
        // $checkStatus = isset($datas['checkStatus']) ? $datas['checkStatus'] : null;

        $category = new Category();
        $category->setName($title);
        $category->setStatus($status);

        if($priority){
            $category->setPriority($priority);
        }
        

        if (!empty($parent_id)) {
            $parent = $categoryRepository->find($parent_id);
            if (empty($parent)) {
                return new Response('分类不存在', 500);
            }
            $category->setParentCategory($parent);
        }

        
        $this->entityPersist($category);

        // 默认排序值
        if( !$priority ){
            $category->setPriority( $category->getId() );
            $this->entityPersist($category);
        }

        return CommonUtil::resultData([])->toJsonResponse();
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
        $form->get('status')->setData(array_search($category->getStatusText(), Category::$statuses));
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
        $previewImageFile = $category->getPreviewImageFile();
        if ($previewImageFile) {
            $filePreviewArray[$previewImageFile->getId()] = [
                'id' => $previewImageFile->getId(),
                'fileId' => $previewImageFile->getId(),
                'priority' => 0,
                'name' => $previewImageFile->getName(),
                'size' => $previewImageFile->getSize()
            ];

            $form->get('previewImageFile')->setData($filePreviewArray);
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
                if (empty($iconFile)) {
                    return new Response('页面错误', 500);
                }
            }

            $previewImageFileId = $request->request->get('category')['previewImageFile'] ?? null;
            $previewImageFile = null;
            if (!empty($previewImageFileId)) {
                $previewImageFile = $fileRepository->find($previewImageFileId);
                if (empty($previewImageFile)) {
                    return new Response('页面错误', 500);
                }
            }

            $category->setIconFile($iconFile);
            $category->setPreviewImageFile($previewImageFile);

            $status = $request->request->get('category')['status'];
            $category->setStatus($status);

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
