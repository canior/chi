<?php

namespace App\Controller\Backend;

use App\Entity\CourseStudent;
use App\Form\CourseStudentType;
use App\Repository\CourseRepository;
use App\Repository\CourseStudentRepository;
use App\Entity\Subject;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Course;
use Endroid\QrCode\Factory\QrCodeFactory;
/**
 * @Route("/backend")
 */
class OfflineCourseStudentController extends BackendController
{
    /**
     * @Route("/offlineCourse/student/statistic", name="offline_course_student_statistic_index", methods="GET")
     * @param Request $request
     * @param CourseStudentRepository $courseStudentRepository
     * @param CourseRepository $courseRepository
     * @return Response
     */
    public function statisticIndex(Request $request, CourseStudentRepository $courseStudentRepository, CourseRepository $courseRepository): Response
    {
        $data = [
            'title' => '报到管理',
            'subjects' => Subject::$subjectTextArray,
            'user' => $this->getUser(),
            'form' => [
                'subject' => $request->query->get('subject', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];

        if($data['form']['subject']) {
            $data['data'] = $courseRepository->findBy(['subject' => $data['form']['subject']]);
        } else {
            $data['data'] = $courseRepository->findAll();
        }

        if ($this->getUser()->isSecurity()) {
            if($data['form']['subject']) {
                $data['data'] = $courseRepository->findBy(['subject' => $data['form']['subject'], 'ownerUser' => $this->getUser()]);
            } else {
                $data['data'] = $courseRepository->findBy(['ownerUser' => $this->getUser()]);
            }
        }


        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/offline_course_student/statistic.html.twig', $data);
    }

    /**
     * @Route("/offlineCourse/student/{courseId}", name="offline_course_student_index", methods="GET")
     * @param Request $request
     * @param $courseId
     * @param CourseStudentRepository $courseStudentRepository
     * @return Response
     */
    public function index(Request $request, $courseId, CourseStudentRepository $courseStudentRepository): Response
    {
        $data = [
            'title' => '报到管理',
        ];

        $course = $this->getEntityManager()->getRepository(Course::class)->find($courseId);
        $data['course'] = $course;
        return $this->render('backend/offline_course_student/index.html.twig', $data);
    }

    /**
     * @Route("/offlineCourse/student/{courseId}/count", name="offline_course_student_count", methods="GET")
     * @param CourseStudentRepository $courseStudentRepository
     * @param $courseId
     * @return Response
     */
    public function countStudent(CourseStudentRepository $courseStudentRepository, $courseId) {
        /**
         * @var Course $course
         */
        $course = $this->getEntityManager()->getRepository(Course::class)->find($courseId);
        $total = $courseStudentRepository->count(['course' => $courseId]);
        $registedTotal = $course->getTotalRegisteredStudentUsers();
        $welcomedTotal = $course->getTotalWelcomeStudentUsers();
        $json = json_encode([
            'total' => $total,
            'registeredTotal' => $registedTotal,
            'welcomedTotal' => $welcomedTotal
        ]);
        return new Response($json);
    }

    /**
     * @Route("/offlineCourse/student/{courseId}/studentTable", name="offline_course_student_table", methods="GET")
     * @param CourseStudentRepository $courseStudentRepository
     * @param $courseId
     * @return Response
     */
    public function studentTable(CourseStudentRepository $courseStudentRepository, $courseId) {
        $courseStudents = $courseStudentRepository->findBy(['course' => $courseId], ['id' => 'desc']);
        return $this->render('backend/offline_course_student/table.html.twig', [
            'user' => $this->getUser(),
            'courseStudents' => $courseStudents,
            'totalCourseStudents' => count($courseStudents)
        ]);
    }

    /**
     * @Route("/offlineCourse/student/{id}/qr/{status}", name="offline_course_student_create_qr", methods="GET")
     * @param QrCodeFactory $qrCodeFactory
     * @param string $id courseId
     * @param $status
     * @return Response
     */
    public function createQRAction(QrCodeFactory $qrCodeFactory, $id, $status) {
        // 加上微信扫一扫的图标
        $url = "https://jinqiu.yunlishuju.com/wx?";
        /**
         * @var QrCode $qrCode
         */
        $qrCode = $qrCodeFactory->create($url . "courseId=" . $id . "&status=" . $status);
        return new Response($qrCode->writeString(), Response::HTTP_OK, ['Content-Type' => $qrCode->getContentType()]);
    }

    /**
     * @Route("/offlineCourse/student/{courseId}/new", name="offline_course_student_new", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $courseStudent = new CourseStudent();
        $form = $this->createForm(CourseStudentType::class, $courseStudent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($courseStudent);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('offline_course_student_index');
        }

        return $this->render('backend/offline_course_student/new.html.twig', [
            'course_student' => $courseStudent,
            'title' => '添加 CourseStudent',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/offlineCourse/student/{id}/edit", name="offline_course_student_edit", methods="GET|POST")
     * @param Request $request
     * @param CourseStudent $courseStudent
     * @return Response
     */
    public function edit(Request $request, CourseStudent $courseStudent): Response
    {
        $form = $this->createForm(CourseStudentType::class, $courseStudent);
        $form->get('status')->setData(array_search($courseStudent->getStatusText(), CourseStudent::$statusTexts));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('course_student')['status'];
            $courseStudent->setStatus($status);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('offline_course_student_edit', ['id' => $courseStudent->getId()]);
        }

        return $this->render('backend/offline_course_student/edit.html.twig', [
            'course_student' => $courseStudent,
            'title' => '修改课程报到签到记录',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/offlineCourse/student/{id}", name="offline_course_student_delete", methods="DELETE")
     * @param Request $request
     * @param CourseStudent $courseStudent
     * @return Response
     */
    public function delete(Request $request, CourseStudent $courseStudent): Response
    {
        $courseId = $courseStudent->getCourse()->getId();
        if ($this->isCsrfTokenValid('delete'.$courseStudent->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($courseStudent);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('offline_course_student_index', ['courseId' => $courseId]);
    }
}
