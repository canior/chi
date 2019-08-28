<?php

namespace App\Controller\Backend;

use App\Entity\Category;
use App\Entity\Course;
use App\Entity\File;
use App\Entity\Subject;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\TeacherRepository;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Command\Product\Image\CreateOrUpdateProductImagesCommand;
use App\Entity\Product;
use App\Command\Product\Spec\Image\CreateOrUpdateProductSpecImagesCommand;
use App\Service\Util\CommonUtil;
use App\Repository\CategoryRepository;
use App\Entity\Teacher;

/**
 * @Route("/backend")
 */
class CourseController extends BackendController
{
    /**
     * @Route("/course/", name="course_index", methods="GET")
     * @param CourseRepository $courseRepository
     * @param Request $request
     * @return Response
     */
    public function index(Request $request, CourseRepository $courseRepository): Response
    {
        // NG
        $data = [];
        if( $request->query->get('isNg') ){
            $data = [
                'title' => '课程管理',
                'form' => [
                    'subject' => $request->query->get('subject', null),
                    'courseShowType' => $request->query->get('courseShowType', 'all'),
                    'oneCategory' => $request->query->get('oneCategory', null),
                    'twoCategory' => $request->query->get('twoCategory', null),
                    'page' => $request->query->getInt('page', 1)
                ],
                'courseShowTypes' => Course::$courseShowTypeTexts,
                'oneCategoryList' => json_encode(FactoryUtil::categoryRepository()->getCategoryTree(0, true)),
            ];

            $data['data'] = $courseRepository->findCourseQueryBuild(true, $data['form']['courseShowType'], $data['form']['oneCategory'], $data['form']['twoCategory']);

            $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);



            


            $datas['courses']  = [];
            foreach ($data['pagination'] as $k => $v) {
                $datas['courses'][] = $v->getListArray();
            }


            $datas['category'] = $this->getTempTree( $categoryRepository->getCategoryList() );

            $datas['teacher'] = $teacherRepository->getTeacherList();

            $datas['total_page'] = 8;

            return CommonUtil::resultData($datas)->toJsonResponse();
        }

        return $this->render('backend/course/index.html.twig', $data);
    }

    /**
     * @Route("/course/new", name="course_new", methods="GET|POST")
     * @param Request $request
     * @param TeacherRepository $teacherRepository
     * @return Response
     */
    public function new(Request $request, TeacherRepository $teacherRepository,CategoryRepository $categoryRepository): Response
    {

        $datas = json_decode($request->getContent(), true);
        
        
        $title = isset($datas['title']) ? $datas['title'] : null;
        $cost_type = isset($datas['cost_type']) ? $datas['cost_type'] : null;
        $collect_timelong = isset($datas['collect_timelong']) ? $datas['collect_timelong'] : null;
        $collect_num = isset($datas['collect_num']) ? $datas['collect_num'] : null;
        $price = isset($datas['price']) ? $datas['price'] : null;
        $remark = isset($datas['remark']) ? $datas['remark'] : null;
        $content = isset($datas['content']) ? $datas['content'] : null;
        $show_type = isset($datas['show_type']) ? $datas['show_type'] : null;


        $course = new Course();

        $course->setTitle($title);
        $course->setUnlockType($cost_type);
        $course->setPrice($price);
        $course->setTotalGroupUserOrdersRequired($collect_num);
        $course->setGroupOrderValidForHours($collect_timelong);

        $course->setShortDescription($content);
        $course->setCourseShowType($show_type);


        $status = isset($datas['status']) ? $datas['status'] : 'active';
        $course->setStatus($status);




        $aliyunVideoId = isset($datas['video_key']) ? $datas['video_key'] : null;
        if($aliyunVideoId){
            $course->setAliyunVideoId($aliyunVideoId);
        }

        $teacher_id = isset($datas['teacher_id']) ? $datas['teacher_id'] : null;
        if($teacher_id){
            $teacher = $teacherRepository->find($teacher_id);
            $course->setTeacher($teacher);
        }

        $subject = isset($datas['subject']) ? $datas['subject'] : null;
        $course->setSubject($subject);

        $course_tag = isset($datas['course_tag']) ? $datas['course_tag'] : null;
        $course->setCourseTag($course_tag);

        $category_id = isset($datas['category_id']) ? $datas['category_id'] : null;
        if($category_id){
            $category = $categoryRepository->find($category_id);
            $course->setCourseCategory ($category);
        }


        // dump( $course );die;
        // $course->setUnlockType($unlockType);
        // $course->setCourseShowType($courseShowType);

        //假如选择一级分类默认创建一个二级的单课类别
        if (!empty($course->getCourseCategory())) {
            if (empty($course->getCourseCategory()->getParentCategory())) {
                $categoryActual = Category::factory($course->getTitle(), $course->getCourseCategory());
                $categoryActual->setSingleCourse(1);
                $categoryActual->setPriority($course->getPriority());
                $categoryActual->setStatus($status);
                $this->entityPersist($categoryActual, false);
                $course->setCourseActualCategory($categoryActual);
            } else {
                $course->setCourseActualCategory($course->getCourseCategory());
            }
        }




        //update preview image
        $previewImageFileId = isset($datas['image']) ? $datas['image'] : null;
        if ($previewImageFileId) {
            $previewImageFile = $this->getEntityManager()->getRepository(File::class)->find($previewImageFileId);
            $course->setPreviewImageFile($previewImageFile);
        } else {
            $course->setPreviewImageFile(null);
        }

        $shareImageFileId = isset($datas['share_image']) ? $datas['share_image'] : null;
        if ($shareImageFileId) {
            $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
            $course->setShareImageFile($shareImageFile);
        } else {
            $course->setShareImageFile(null);
        }




        $this->entityPersist($course);



        $course->setPriority( $course->getId() );
        $this->entityPersist($course);

        

        // $product = new Product();
        // $product->setCourse($course);
        // $product->setTitle($title);
        // $this->entityPersist($product);

        return CommonUtil::resultData([])->toJsonResponse();
    }


    /**
     * @Route("/course/update", name="course_update", methods="GET|POST")
     * @param Request $request
     * @param TeacherRepository $teacherRepository
     * @return Response
     */
    public function update(Request $request, CourseRepository $courseRepository, TeacherRepository $teacherRepository,CategoryRepository $categoryRepository): Response
    {

        $datas = json_decode($request->getContent(), true);
        

        $id = isset($datas['id']) ? $datas['id'] : null;
        $title = isset($datas['title']) ? $datas['title'] : null;
        $unlockType = isset($datas['unlockType']) ? $datas['unlockType'] : null;
        $course = $courseRepository->find($id);

        $course->setTitle($title);

        
        $status = isset($datas['status']) ? $datas['status'] : 'active';
        $course->setStatus($status);


        $aliyunVideoId = isset($datas['video_key']) ? $datas['video_key'] : null;
        if($aliyunVideoId){
            $course->setAliyunVideoId($aliyunVideoId);
        }

        $teacher_id = isset($datas['teacher_id']) ? $datas['teacher_id'] : null;
        if($teacher_id){
            $teacher = $teacherRepository->find($teacher_id);
            $course->setTeacher($teacher);
        }

        $subject = isset($datas['subject']) ? $datas['subject'] : null;
        $course->setSubject($subject);

        $course_tag = isset($datas['course_tag']) ? $datas['course_tag'] : null;
        $course->setCourseTag($course_tag);

        $category_id = isset($datas['category_id']) ? $datas['category_id'] : null;
        if($category_id){
            $category = $categoryRepository->find($category_id);
            $course->setCourseCategory ($category);
        }

        $unlockType = isset($datas['cost_type']) ? $datas['cost_type'] : null;
        $collect_timelong = isset($datas['collect_timelong']) ? $datas['collect_timelong'] : null;
        $collect_num = isset($datas['collect_num']) ? $datas['collect_num'] : null;
        $price = isset($datas['price']) ? $datas['price'] : null;
        $remark = isset($datas['remark']) ? $datas['remark'] : null;
        $content = isset($datas['content']) ? $datas['content'] : null;
        $show_type = isset($datas['show_type']) ? $datas['show_type'] : null;
        $priority = isset($datas['priority']) ? $datas['priority'] : 0;


        $course->setPriority($priority);
        $course->setUnlockType($unlockType);
        $course->setPrice($price);
        $course->setTotalGroupUserOrdersRequired($collect_num);
        $course->setGroupOrderValidForHours($collect_timelong);

        $course->setShortDescription($content);
        $course->setCourseShowType($show_type);

        // dump( $course );die;
        // $course->setUnlockType($unlockType);
        // $course->setCourseShowType($courseShowType);

        //假如选择一级分类默认创建一个二级的单课类别
        if (!empty($course->getCourseCategory())) {
            if (empty($course->getCourseCategory()->getParentCategory())) {
                $categoryActual = Category::factory($course->getTitle(), $course->getCourseCategory());
                $categoryActual->setSingleCourse(1);
                $categoryActual->setPriority($course->getPriority());
                $categoryActual->setStatus($status);
                $this->entityPersist($categoryActual, false);
                $course->setCourseActualCategory($categoryActual);
            } else {
                $course->setCourseActualCategory($course->getCourseCategory());
            }
        }


        //update preview image
        $previewImageFileId = isset($datas['image']) ? $datas['image'] : null;
        if ($previewImageFileId) {
            $previewImageFile = $this->getEntityManager()->getRepository(File::class)->find($previewImageFileId);
            $course->setPreviewImageFile($previewImageFile);
        } else {
            $course->setPreviewImageFile(null);
        }

        $shareImageFileId = isset($datas['share_image']) ? $datas['share_image'] : null;
        if ($shareImageFileId) {
            $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
            $course->setShareImageFile($shareImageFile);
        } else {
            $course->setShareImageFile(null);
        }
        

        $this->entityPersist($course);



        // $product = new Product();
        // $product->setCourse($course);
        // $product->setTitle($title);
        // $this->entityPersist($product);

        return CommonUtil::resultData([])->toJsonResponse();
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
     * @Route("/course/create", name="courseCreate", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request, CourseRepository $courseRepository,CategoryRepository $categoryRepository, TeacherRepository $teacherRepository): Response{

        $id = $request->get('id', null);
        if( $id ){
            $data['course'] = $courseRepository->find($id)->getLittleArray();
        }

        $data['category'] = $this->getTempTree( $categoryRepository->getCategoryList() );

        $data['teacher'] = $teacherRepository->getTeacherList();

        return CommonUtil::resultData($data)->toJsonResponse();

    }

    

    /**
     * @Route("/course/delete/{id}", name="courseDelete", methods="GET|POST")
     * @param Request $request
     * @param Course $course
     */
    public function delete(Request $request, Course $course): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($course);
        $em->flush();
        return CommonUtil::resultData([])->toJsonResponse();
    }

    /**
     * 推荐免费专区
     * @param Request $request
     * @param Course $course
     * @return Response
     * @Route("/course/recommend/free/{id}", name="recommendFreeZone", methods="POST")
     * @author zxqc2018
     */
    public function recommendFreeZone(Request $request, Course $course): Response
    {
        if ($course->getCourseActualCategory()->isShowFreeZone()) {
            $course->getCourseActualCategory()->setShowFreeZone(0);
            $noticeStr = '下免费专区成功';
        } else {
            $course->getCourseActualCategory()->setShowFreeZone(1);
            $noticeStr = '上免费专区成功';
        }

        $this->entityPersist($course->getCourseActualCategory());
        $this->addFlash('notice', $noticeStr);
        $formData = [
            'courseShowType' => $request->request->get('courseShowType', 'all'),
            'oneCategory' => $request->request->get('oneCategory', null),
            'twoCategory' => $request->request->get('twoCategory', null),
            'page' => $request->request->getInt('page', 1)
        ];
        return $this->redirectToRoute('course_index', $formData);
    }

    /**
     * 推荐首页
     * @param Request $request
     * @param Course $course
     * @return Response
     * @Route("/course/recommend/home/{id}", name="recommendHomeZone", methods="POST")
     * @author zxqc2018
     */
    public function recommendHomeZone(Request $request, Course $course): Response
    {
        if ($course->getCourseActualCategory()->isShowRecommendZone()) {
            $course->getCourseActualCategory()->setShowRecommendZone(0);
            $noticeStr = '下推荐专区成功';
        } else {
            $course->getCourseActualCategory()->setShowRecommendZone(1);
            $noticeStr = '上推荐专区成功';
        }

        $this->entityPersist($course->getCourseActualCategory());
        $this->addFlash('notice', $noticeStr);
        $formData = [
            'courseShowType' => $request->request->get('courseShowType', 'all'),
            'oneCategory' => $request->request->get('oneCategory', null),
            'twoCategory' => $request->request->get('twoCategory', null),
            'page' => $request->request->getInt('page', 1)
        ];
        return $this->redirectToRoute('course_index', $formData);
    }

    /**
     * 推荐首页最新课程
     * @param Request $request
     * @param Course $course
     * @return Response
     * @Route("/course/recommend/newest/{id}", name="recommendNewestZone", methods="POST")
     * @author zxqc2018
     */
    public function recommendNewestZone(Request $request, Course $course): Response
    {
        if ($course->isShowNewest()) {
            $course->setIsShowNewest(0);
            $noticeStr = '下最新课程专区成功';
        } else {
            $course->setIsShowNewest(1);
            $noticeStr = '上最新课程专区成功';
        }

        $this->entityPersist($course);
        $this->addFlash('notice', $noticeStr);
        $formData = [
            'courseShowType' => $request->request->get('courseShowType', 'all'),
            'oneCategory' => $request->request->get('oneCategory', null),
            'twoCategory' => $request->request->get('twoCategory', null),
            'page' => $request->request->getInt('page', 1)
        ];
        return $this->redirectToRoute('course_index', $formData);
    }
}
