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
use App\Entity\Product;

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

            $where = $request->query->all();

            $categoryQuery = $categoryRepository->categoryQuery($where);

            $page = isset($where['page']) ? $where['page'] : 1;
            $limit = isset($where['num']) ? $where['num'] : self::PAGE_LIMIT;
            $category = $this->getPaginator()->paginate($categoryQuery, $page, $limit);

            $datas['categorys']  = [];
            foreach ($category as $k => $v) {
                $datas['categorys'][] = $v->getLittleArray();
            }
            
            $total = $categoryRepository->categoryQuery($where,true)->getQuery()->getSingleScalarResult();
            $datas['total_page'] = ceil($total/$limit);


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
            $data['category'] = $categoryRepository->find($id)->getLittleArray();
        }

        $data['categorys'] = $this->getTempTree( $categoryRepository->getCategoryList() );

        return CommonUtil::resultData($data)->toJsonResponse();

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
        $video_key = isset($datas['video_key']) ? $datas['video_key'] : null;
        $priority = isset($datas['priority']) ? $datas['priority'] : null;
        $parent_id = isset($datas['parent_id']) ? $datas['parent_id'] : null;
        $remark = isset($datas['remark']) ? $datas['remark'] : null;


        $category = new Category();
        $category->setName($title);
        $category->setStatus($status);
        $category->setAliyunVideoId($video_key);
        $category->setShortDescription($remark);

        if($priority){
            $category->setPriority($priority);
        }
        
        if (!empty($parent_id)) {
            $parent = $categoryRepository->find($parent_id);
            $category->setParentCategory($parent);
        }

        //update preview image
        $previewImageFileId = isset($datas['preview_image']) ? $datas['preview_image'] : null;
        if ($previewImageFileId) {
            $previewImageFile = $this->getEntityManager()->getRepository(File::class)->find($previewImageFileId);
            $category->setPreviewImageFile($previewImageFile);
        } else {
            $category->setPreviewImageFile(null);
        }

        $remarkImageFileId = isset($datas['remark_image']) ? $datas['remark_image'] : null;
        if ($remarkImageFileId) {
            $remarkImageFile = $this->getEntityManager()->getRepository(File::class)->find($remarkImageFileId);
            $category->setIconFile($remarkImageFile);
        } else {
            $category->setIconFile(null);
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
     * @Route("/category/update", name="category_update", methods="GET|POST")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @param FileRepository $fileRepository
     * @return Response
     */
    public function update(Request $request, CategoryRepository $categoryRepository): Response
    {

        $datas = json_decode($request->getContent(), true);

        $title = isset($datas['title']) ? $datas['title'] : null;
        $status = isset($datas['status']) ? $datas['status'] : 'active';
        $video_key = isset($datas['video_key']) ? $datas['video_key'] : null;
        $priority = isset($datas['priority']) ? $datas['priority'] : null;
        $parent_id = isset($datas['parent_id']) ? $datas['parent_id'] : null;
        $remark = isset($datas['remark']) ? $datas['remark'] : null;

        $category = new Category();
        $category->setName($title);
        $category->setStatus($status);
        $category->setAliyunVideoId($video_key);
        $category->setShortDescription($remark);

        if($priority){
            $category->setPriority($priority);
        }
        
        if (!empty($parent_id)) {
            $parent = $categoryRepository->find($parent_id);
            $category->setParentCategory($parent);
        }

        //update preview image
        $previewImageFileId = isset($datas['preview_image']) ? $datas['preview_image'] : null;
        if ($previewImageFileId) {
            $previewImageFile = $this->getEntityManager()->getRepository(File::class)->find($previewImageFileId);
            $category->setPreviewImageFile($previewImageFile);
        } else {
            $category->setPreviewImageFile(null);
        }

        $remarkImageFileId = isset($datas['remark_image']) ? $datas['remark_image'] : null;
        if ($remarkImageFileId) {
            $remarkImageFile = $this->getEntityManager()->getRepository(File::class)->find($remarkImageFileId);
            $category->setIconFile($remarkImageFile);
        } else {
            $category->setIconFile(null);
        }

        $this->entityPersist($category);

        return CommonUtil::resultData([])->toJsonResponse();
    }

    

    /**
     * @Route("/category/delete/{id}", name="categoryDelete", methods="GET|POST")
     * @param Request $request
     * @param Course $course
     */
    public function delete(Request $request, Category $category): Response
    {
        $category->setIsDeleted(1);
        $this->entityPersist($category);
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * @Route("/category/dispose/", name="categoryOption", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function dispose(Request $request, CategoryRepository $categoryRepository): Response{

        $datas = json_decode($request->getContent(), true);
        $ids = isset($datas['ids']) ? $datas['ids'] : [];
        $action = isset($datas['action']) ? $datas['action'] : null;
        switch ($action) {
            case 'publish_true':
                # 发布
                foreach ($ids as $v) {
                    $category = $categoryRepository->find($v);
                    $category->setStatus(Product::ACTIVE);
                    $this->entityPersist($category);
                }
                $msg = '发布成功';
                break;
            case 'publish_false':
                # 未发布
                foreach ($ids as $v) {
                    $category = $categoryRepository->find($v);
                    $category->setStatus(Product::INACTIVE);
                    $this->entityPersist($category);
                }
                $msg = '未发布成功';
                break;
            case 'deleted_true':
                # 删除
                foreach ($ids as $v) {
                    $category = $categoryRepository->find($v);
                    $category->setIsDeleted(1);
                    $this->entityPersist($category);
                }
                $msg = '删除成功';
                break;
            default:
                break;
        }
 
        return CommonUtil::resultData(['msg'=>$msg])->toJsonResponse();
    }
}
