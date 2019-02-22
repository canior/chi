<?php

namespace App\Controller\Backend;

use App\Entity\Course;
use App\Entity\File;
use App\Entity\ProductImage;
use App\Entity\Subject;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\TeacherRepository;
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
        $data = [
            'title' => '课程管理',
            'subjects' => Subject::$subjectTextArray,
            'form' => [
                'subject' => $request->query->get('subject', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        if ($data['form']['subject']){
            $data['data'] = $courseRepository->findBy(['subject' => $data['form']['subject']]);
        } else {
            $data['data'] = $courseRepository->findAll();
        }

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
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

        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $status = $request->request->get('course')['status'];
            $subject = $request->request->get('course')['subject'];
            $course->setStatus($status);
            $course->setSubject($subject);

            $em = $this->getDoctrine()->getManager();
            $em->persist($course);
            $em->flush();

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

            $this->addFlash('notice', '创建成功');
            return $this->redirectToRoute('course_index');
        }

        return $this->render('backend/course/new.html.twig', [
            'course' => $course,
            'title' => '创建新课程',
            'form' => $form->createView(),
        ]);
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
        $form->get('subject')->setData(array_search($course->getSubjectText(), Subject::$subjectTextArray));
        $form->get('status')->setData(array_search($course->getProduct()->getStatusText(), Product::$statuses));

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

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subject = $request->request->get('course')['subject'];
            $status = $request->request->get('course')['status'];
            $course->setSubject($subject);
            $course->setStatus($status);
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
     * @Route("/course/{id}", name="course_delete", methods="DELETE")
     */
    public function delete(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($course);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('course_index');
    }
}
