<?php

namespace App\Controller\Backend;

use App\Entity\Course;
use App\Entity\File;
use App\Entity\ProductImage;
use App\Entity\Subject;
use App\Form\OfflineCourseType;
use App\Repository\CourseRepository;
use App\Repository\TeacherRepository;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use App\Command\Product\Image\CreateOrUpdateProductImagesCommand;
use App\Entity\Product;
use App\Command\Product\Spec\Image\CreateOrUpdateProductSpecImagesCommand;

/**
 * @Route("/backend")
 */
class OfflineCourseController extends BackendController
{
    /**
     * @Route("/offlineCourse/", name="offline_course_index", methods="GET")
     * @param Request $request
     * @param CourseRepository $courseRepository
     * @param TeacherRepository $teacherRepository
     * @return Response
     */
    public function index(Request $request, CourseRepository $courseRepository, TeacherRepository $teacherRepository): Response
    {
        $teacherList = CommonUtil::two2one(CommonUtil::entityArray2DataArray($teacherRepository->findAll()), ['id' => 'name']);
        $data = [
            'title' => '活动管理',
            'form' => [
                'subject' => $request->query->get('subject', null),
                'address' => $request->query->get('address', null),
                'status' => $request->query->get('status', null),
                'teacher' => $request->query->get('teacher', null),
                'createdAtStart' => $request->query->get('createdAtStart', null),
                'createdAtEnd' => $request->query->get('createdAtEnd', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'subjects' => Subject::$subjectTextArray,
            'teachers' => $teacherList,
            'statuses' => Product::$statuses,
        ];

        $data['data'] = $courseRepository->findOfflineCourseQueryBuild($data['form']);

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);

        return $this->render('backend/offline_course/index.html.twig', $data);
    }

    /**
     * @Route("/offlineCourse/new", name="offline_course_new", methods="GET|POST")
     * @param Request $request
     * @param TeacherRepository $teacherRepository
     * @return Response
     */
    public function new(Request $request, TeacherRepository $teacherRepository): Response
    {

        $course = new Course();
        $course->setOffline();
        $form = $this->createForm(OfflineCourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!empty($course->getRefCourse())) {
                if (!empty($course->getRefCourse()->getRefCourse())) {
                    return new Response('关联活动已经被关联', 500);
                }

                $systemSubject = [Subject::SYSTEM_1, Subject::SYSTEM_2];
                $systemTradeSubject = [Subject::TRADING];
                $thinkingSubject = [Subject::PRIVATE_DIRECTOR, Subject::THINKING];
                if (in_array($course->getSubject(), $thinkingSubject) || in_array($course->getSubject(), $systemSubject) && in_array($course->getRefCourse()->getSubject(), $systemSubject) ||
                    in_array($course->getSubject(), $systemTradeSubject) && in_array($course->getRefCourse()->getSubject(), $systemTradeSubject)
                ) {
                    return new Response('关联活动类型不对', 500);
                }
            }
            $status = $request->request->get('offline_course')['status'];
            $subject = $request->request->get('offline_course')['subject'];
            $course->setStatus($status);
            $course->setSubject($subject);

            CommonUtil::entityPersist($course);

            if (!empty($course->getRefCourse())) {
                //双向关联
                $course->getRefCourse()->setRefCourse($course);
                CommonUtil::entityPersist($course->getRefCourse());
            }

            try {
                $images = isset($request->request->get('offline_course')['images']) ? $request->request->get('offline_course')['images'] : [];
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
                $specImages = isset($request->request->get('offline_course')['specImages']) ? $request->request->get('offline_course')['specImages'] : [];
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

            //add share image
            $shareImageFileId = isset($request->request->get('offline_course')['shareImageFile']) ? $request->request->get('offline_course')['shareImageFile'] : null;
            if ($shareImageFileId) {
                /**
                 * @var File $shareImageFile
                 */
                $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
                $course->getProduct()->setShareImageFile($shareImageFile);
                $this->getEntityManager()->persist($course->getProduct());
                $this->getEntityManager()->flush();
            }
            //add address image
            $addressImageFileId = isset($request->request->get('offline_course')['addressImageFile']) ? $request->request->get('offline_course')['addressImageFile'] : null;
            if ($addressImageFileId) {
                /**
                 * @var File $addressImageFile
                 */
                $addressImageFile = $this->getEntityManager()->getRepository(File::class)->find($addressImageFileId);
                $course->setAddressImageFile($addressImageFile);
                $this->getEntityManager()->persist($course->getProduct());
                $this->getEntityManager()->flush();
            }
            $this->addFlash('notice', '创建成功');
            return $this->redirectToRoute('offline_course_index');
        }

        return $this->render('backend/offline_course/new.html.twig', [
            'course' => $course,
            'title' => '创建新活动',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/offlineCourse/{id}/edit", name="offline_course_edit", methods="GET|POST")
     * @param Request $request
     * @param Course $course
     * @return Response
     */
    public function edit(Request $request, Course $course): Response
    {
        $form = $this->createForm(OfflineCourseType::class, $course);
        $form->get('subject')->setData(array_search($course->getSubjectText(), Subject::$subjectTextArray));
        $form->get('status')->setData(array_search($course->getProduct()->getStatusText(), Product::$statuses));

        //保存原始关联课程
        $originRefCourse = $course->getRefCourse();
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

        $addressImageFile = $course->getAddressImageFile();
        if ($addressImageFile) {
            $addressImageArray[$addressImageFile->getId()] = [
                'id' => $addressImageFile->getId(),
                'fileId' => $addressImageFile->getId(),
                'priority' => 0,
                'name' => $addressImageFile->getName(),
                'size' => $addressImageFile->getSize()
            ];
            $form->get('addressImageFile')->setData($addressImageArray);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!empty($course->getRefCourse())) {
                if ($course->getRefCourse() === $course) {
                    return new Response('自己不能关联自己', 500);
                }

                if (!empty($course->getRefCourse()->getRefCourse()) && $course->getRefCourse()->getRefCourse() !== $course) {
                    return new Response('关联活动已经被关联', 500);
                }
                $systemSubject = [Subject::SYSTEM_1, Subject::SYSTEM_2];
                $systemTradeSubject = [Subject::TRADING];
                $thinkingSubject = [Subject::PRIVATE_DIRECTOR, Subject::THINKING];
                if (in_array($course->getSubject(), $thinkingSubject) || in_array($course->getSubject(), $systemSubject) && in_array($course->getRefCourse()->getSubject(), $systemSubject) ||
                    in_array($course->getSubject(), $systemTradeSubject) && in_array($course->getRefCourse()->getSubject(), $systemTradeSubject)
                ) {
                    return new Response('关联活动类型不对', 500);
                }

                if (!empty($originRefCourse)) {
                    if ($originRefCourse !== $course->getRefCourse()) {
                        $originRefCourse->setRefCourse(null);
                        CommonUtil::entityPersist($originRefCourse);
                        $course->getRefCourse()->setRefCourse($course);
                        CommonUtil::entityPersist($course->getRefCourse());
                    }
                } else {
                    $course->getRefCourse()->setRefCourse($course);
                    CommonUtil::entityPersist($course->getRefCourse());
                }
            } else {
                if (!empty($originRefCourse)) {
                    $originRefCourse->setRefCourse(null);
                    CommonUtil::entityPersist($originRefCourse);
                }
            }

            $subject = $request->request->get('offline_course')['subject'];
            $status = $request->request->get('offline_course')['status'];
            $course->setSubject($subject);
            $course->setStatus($status);
            $this->getEntityManager()->persist($course);

            try {
                $images = isset($request->request->get('offline_course')['images']) ? $request->request->get('offline_course')['images'] : [];
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
                $specImages = isset($request->request->get('offline_course')['specImages']) ? $request->request->get('offline_course')['specImages'] : [];
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
            $shareImageFileId = isset($request->request->get('offline_course')['shareImageFile']) ? $request->request->get('offline_course')['shareImageFile'] : [];
            if ($shareImageFileId) {
                /**
                 * @var File $shareImageFile
                 */
                $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
                $course->setShareImageFile($shareImageFile);

            } else {
                $course->setShareImageFile(null);
            }

            //update share image
            $addressImageFileId = isset($request->request->get('offline_course')['addressImageFile']) ? $request->request->get('offline_course')['addressImageFile'] : [];
            if ($addressImageFileId) {
                /**
                 * @var File $addressImageFile
                 */
                $addressImageFile = $this->getEntityManager()->getRepository(File::class)->find($addressImageFileId);
                $course->setAddressImageFile($addressImageFile);

            } else {
                $course->setAddressImageFile(null);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('offline_course_edit', ['id' => $course->getId()]);
        }

        return $this->render('backend/offline_course/edit.html.twig', [
            'course' => $course,
            'title' => '编辑活动',
            'form' => $form->createView(),
        ]);
    }
}
