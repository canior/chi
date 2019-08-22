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

            $datas  = [];
            foreach ($data['pagination'] as $k => $v) {
                $datas[] = $v->getLittleArray();
            }
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
    public function new(Request $request, TeacherRepository $teacherRepository): Response
    {

        $datas = json_decode($request->getContent(), true);
        $title = isset($datas['title']) ? $datas['title'] : null;
        $status = isset($datas['status']) ? $datas['status'] : 'active';
        $subject = isset($datas['subject']) ? $datas['subject'] : null;
        $unlockType = isset($datas['unlockType']) ? $datas['unlockType'] : null;
        $courseShowType = isset($datas['courseShowType']) ? $datas['courseShowType'] : null;
        $checkStatus = isset($datas['checkStatus']) ? $datas['checkStatus'] : null;


        $teacher_id = isset($datas['teacher']) ? $datas['teacher'] : null;
        $teacher = $teacherRepository->find($teacher_id);

        $course = new Course();
        $course->setCourseTag($title);
        $course->setStatus($status);
        $course->setTeacher($teacher);
        $course->setSubject($subject);

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

        $this->entityPersist($course);

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
     * @Route("/course/{id}/edit", name="course_edit", methods="GET|POST")
     * @param Request $request
     * @param Course $course
     * @return Response
     */
    public function edit(Request $request, Course $course): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->get('status')->setData(array_search($course->getProduct()->getStatusText(), Product::$statuses));
        $form->get('subject')->setData(array_search($course->getSubjectText(), Subject::$subjectTextArray));
        $form->get('unlockType')->setData(array_search($course->getUnlockTypeText(), Course::$unlockTypeTexts));
        $form->get('courseShowType')->setData(array_search($course->getCourseShowTypeText(), Course::$courseShowTypeTexts));

        /**
         * @var Category $originCourseCategory
         */
        $originCourseCategory = $form->get('courseCategory')->getData();

        $originCourseTitle = $form->get('title')->getData();

        $originCoursePriority = $form->get('priority')->getData();

        // init images
        $productImages = $course->getCourseImages();
        if (!$productImages->isEmpty()) {
            $images = [];
            foreach ($productImages as $image) {
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

        $productSpecImages = $course->getCourseSpecImages();
        if (!$productSpecImages->isEmpty()) {
            $images = [];
            foreach ($productSpecImages as $image) {
                $images[$image->getFile()->getId()] = [
                    'id' => $image->getId(),
                    'fileId' => $image->getFile()->getId(),
                    'priority' => $image->getPriority(),
                    'name' => $image->getFile()->getName(),
                    'size' => $image->getFile()->getSize()
                ];
            }
            $form->get('specImages')->setData($images);
        }

        $shareImageFile = $course->getShareImageFile();
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

        $previewImageFile = $course->getPreviewImageFile();
        if ($previewImageFile) {
            $fileImgArray[$previewImageFile->getId()] = [
                'id' => $previewImageFile->getId(),
                'fileId' => $previewImageFile->getId(),
                'priority' => 0,
                'name' => $previewImageFile->getName(),
                'size' => $previewImageFile->getSize()
            ];
            $form->get('previewImageFile')->setData($fileImgArray);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('course')['status'];
            $subject = $request->request->get('course')['subject'];
            $unlockType = $request->request->get('course')['unlockType'];
            $courseShowType = $request->request->get('course')['courseShowType'];
            $checkStatus = $request->request->get('course')['checkStatus'];

            //假如课程有改动
            if ($originCourseCategory !== $course->getCourseCategory()) {
                //假如选择一级分类默认创建一个二级的单课类别
                if (!empty($course->getCourseCategory()) && empty($course->getCourseCategory()->getParentCategory())) {
                    //假如原类是单课程类去除 类别表中的category
                    if (!empty($course->getCourseActualCategory()) && $course->getCourseActualCategory()->isSingleCourse()) {
                        $course->getCourseActualCategory()->setName($course->getTitle());
                        $course->getCourseActualCategory()->setPriority($course->getPriority());
                        $course->getCourseActualCategory()->setParentCategory($course->getCourseCategory());
                        $course->getCourseActualCategory()->setStatus($status);
                    } else {
                        $categoryActual = Category::factory($course->getTitle(), $course->getCourseCategory());
                        $categoryActual->setSingleCourse(1);
                        $categoryActual->setPriority($course->getPriority());
                        $categoryActual->setStatus($status);
                        $this->entityPersist($categoryActual, false);
                        $course->setCourseActualCategory($categoryActual);
                    }
                } else {
                    //假如原类是单课程类去除 类别表中的category
                    if (!empty($course->getCourseActualCategory()) && $course->getCourseActualCategory()->isSingleCourse()) {
                        $course->getCourseActualCategory()->setIsDeleted(1);
                        $this->entityPersist($course->getCourseActualCategory(), false);
                    }
                    $course->setCourseActualCategory($course->getCourseCategory());
                }
            }

            //假如课程有改动 单课程
            if ($originCourseTitle != $course->getTitle() && !empty($course->getCourseActualCategory()) && $course->getCourseActualCategory()->isSingleCourse()) {
                $course->getCourseActualCategory()->setName($course->getTitle());
            }

            //假如排序有改动 单课程
            if ($originCoursePriority != $course->getPriority() && !empty($course->getCourseActualCategory()) && $course->getCourseActualCategory()->isSingleCourse()) {
                $course->getCourseActualCategory()->setPriority($course->getPriority());
            }

            //假如状态有改动 单课程 
            if ( !empty($course->getCourseActualCategory()) && $course->getCourseActualCategory()->isSingleCourse()) {
                $course->getCourseActualCategory()->setStatus($status);
            }

            $course->setStatus($status);
            $course->setCheckStatus($checkStatus);
            $course->setSubject($subject);
            $course->setUnlockType($unlockType);
            $course->setCourseShowType($courseShowType);
            $this->getEntityManager()->persist($course);

            try {
                $images = isset($request->request->get('course')['images']) ? $request->request->get('course')['images'] : [];
                $imagesCommand = new CreateOrUpdateProductImagesCommand($course->getProduct()->getId(), $images);
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
                $specImages = isset($request->request->get('course')['specImages']) ? $request->request->get('course')['specImages'] : [];
                $specImagesCommand = new CreateOrUpdateProductSpecImagesCommand($course->getProduct()->getId(), $specImages);
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

            //update share image
            $shareImageFileId = isset($request->request->get('course')['shareImageFile']) ? $request->request->get('course')['shareImageFile'] : [];
            if ($shareImageFileId) {
                /**
                 * @var File $shareImageFile
                 */
                $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
                $course->setShareImageFile($shareImageFile);

            } else {
                $course->setShareImageFile(null);
            }

            //update preview image
            $previewImageFileId = isset($request->request->get('course')['previewImageFile']) ? $request->request->get('course')['previewImageFile'] : [];
            if ($previewImageFileId) {
                /**
                 * @var File $previewImageFile
                 */
                $previewImageFile = $this->getEntityManager()->getRepository(File::class)->find($previewImageFileId);
                $course->setPreviewImageFile($previewImageFile);

            } else {
                $course->setPreviewImageFile(null);
            }

            $this->getEntityManager()->persist($course);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('course_edit', ['id' => $course->getId()]);
        }

        return $this->render('backend/course/edit.html.twig', [
            'course' => $course,
            'title' => '编辑课程',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/course/delete/{id}", name="courseDelete", methods="GET|POST")
     * @param Request $request
     * @param Course $course
     */
    public function deleteData(Request $request, Course $course): Response
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
