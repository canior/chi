<?php

namespace App\Controller\Backend;

use App\Entity\CourseStudent;
use App\Form\CourseStudentType;
use App\Repository\CourseRepository;
use App\Repository\CourseStudentRepository;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Course;
use Endroid\QrCode\Factory\QrCodeFactory;
/**
 * @Route("/backend")
 */
class CourseStudentController extends BackendController
{
    /**
     * @Route("/course/student/statistic", name="course_student_statistic_index", methods="GET")
     * @param Request $request
     * @param CourseStudentRepository $courseStudentRepository
     * @param CourseRepository $courseRepository
     * @return Response
     */
    public function statisticIndex(Request $request, CourseStudentRepository $courseStudentRepository, CourseRepository $courseRepository): Response
    {
        $data = [
            'title' => '注册管理',
            'form' => [
                'page' => $request->query->getInt('page', 1)
            ]
        ];

        $data['data'] = $courseRepository->findAll();

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/course_student/statistic.html.twig', $data);
    }

    /**
     * @Route("/course/student/{courseId}", name="course_student_index", methods="GET")
     * @param Request $request
     * @param $courseId
     * @param CourseStudentRepository $courseStudentRepository
     * @return Response
     */
    public function index(Request $request, $courseId, CourseStudentRepository $courseStudentRepository): Response
    {
        $data = [
            'title' => '注册管理',
        ];

        $course = $this->getEntityManager()->getRepository(Course::class)->find($courseId);
        $data['course'] = $course;
        return $this->render('backend/course_student/index.html.twig', $data);
    }

    /**
     * @Route("/course/student/{courseId}/count", name="course_student_count", methods="GET")
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
        $json = json_encode([
            'total' => $total,
            'registeredTotal' => $registedTotal,
        ]);
        return new Response($json);
    }

    /**
     * @Route("/course/student/{courseId}/studentTable", name="course_student_table", methods="GET")
     * @param CourseStudentRepository $courseStudentRepository
     * @param $courseId
     * @return Response
     */
    public function studentTable(CourseStudentRepository $courseStudentRepository, $courseId) {
        $courseStudents = $courseStudentRepository->findBy(['course' => $courseId], ['id' => 'desc']);
        return $this->render('backend/course_student/table.html.twig', [
            'courseStudents' => $courseStudents,
            'totalCourseStudents' => count($courseStudents)
        ]);
    }

    /**
     * @Route("/course/student/{id}/qr/{status}", name="course_student_create_qr", methods="GET")
     * @param QrCodeFactory $qrCodeFactory
     * @param string $id courseId
     * @param $status
     * @return Response
     */
    public function createQRAction(QrCodeFactory $qrCodeFactory, $id, $status) {
        // 加上微信扫一扫的图标
        /**
         * @var QrCode $qrCode
         */
        $qrCode = $qrCodeFactory->create("courseId=" . $id . "&status=" . $status);
        return new Response($qrCode->writeString(), Response::HTTP_OK, ['Content-Type' => $qrCode->getContentType()]);
    }

    /**
     * @Route("/course/student/{courseId}/new", name="course_student_new", methods="GET|POST")
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
            return $this->redirectToRoute('course_student_index');
        }

        return $this->render('backend/course_student/new.html.twig', [
            'course_student' => $courseStudent,
            'title' => '添加 CourseStudent',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/course/student/{id}/edit", name="course_student_edit", methods="GET|POST")
     * @param Request $request
     * @param $courseId
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
            return $this->redirectToRoute('course_student_edit', ['id' => $courseStudent->getId()]);
        }

        return $this->render('backend/course_student/edit.html.twig', [
            'course_student' => $courseStudent,
            'title' => '修改课程报到签到记录',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/course/student/{id}", name="course_student_delete", methods="DELETE")
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

        return $this->redirectToRoute('course_student_index', ['courseId' => $courseId]);
    }
}
