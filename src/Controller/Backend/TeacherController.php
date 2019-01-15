<?php

namespace App\Controller\Backend;

use App\Entity\Teacher;
use App\Form\TeacherType;
use App\Repository\TeacherRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;

/**
 * @Route("/backend")
 */
class TeacherController extends BackendController
{
    /**
     * @Route("/teacher/", name="teacher_index", methods="GET")
     */
    public function index(TeacherRepository $teacherRepository, Request $request): Response
    {
        $data = [
            'title' => '讲师管理',
            'form' => [
                'name' => $request->query->get('name', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        if ($data['form']['name']) {
            $data['data'] = $teacherRepository->findBy(['name' => $data['form']['name']]);
        }
        else {
            $data['data'] = $teacherRepository->findAll();
        }
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/teacher/index.html.twig', $data);
    }

    /**
     * @Route("/teacher/new", name="teacher_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $teacher = new Teacher();
        $form = $this->createForm(TeacherType::class, $teacher);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teacherAvatarFileId = isset($request->request->get('teacher')['teacherAvatarFile']) ? $request->request->get('teacher')['teacherAvatarFile'] : null;
            /**
             * @var File $avatarFile
             */
            $avatarFile = $this->getEntityManager()->getRepository(File::class)->find($teacherAvatarFileId);
            $teacher->setTeacherAvatarFile($avatarFile);
            $em = $this->getDoctrine()->getManager();
            $em->persist($teacher);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('teacher_index');
        }

        return $this->render('backend/teacher/new.html.twig', [
            'teacher' => $teacher,
            'title' => '创建讲师',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teacher/{id}/edit", name="teacher_edit", methods="GET|POST")
     */
    public function edit(Request $request, Teacher $teacher): Response
    {
        $form = $this->createForm(TeacherType::class, $teacher);

        $image = $teacher->getTeacherAvatarFile();
        if ($image){
            $form->get('teacherAvatarFile')->setData($image);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teacherAvatarFileId = isset($request->request->get('teacher')['teacherAvatarFile']) ? $request->request->get('teacher')['teacherAvatarFile'] : null;
            /**
             * @var File $avatarFile
             */
            $avatarFile = $this->getEntityManager()->getRepository(File::class)->find($teacherAvatarFileId);
            $teacher->setTeacherAvatarFile($avatarFile);
            $this->getEntityManager()->persist($teacher);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('teacher_edit', ['id' => $teacher->getId()]);
        }

        return $this->render('backend/teacher/edit.html.twig', [
            'teacher' => $teacher,
            'title' => '编辑讲师',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teacher/{id}", name="teacher_delete", methods="DELETE")
     */
    public function delete(Request $request, Teacher $teacher): Response
    {
        if ($this->isCsrfTokenValid('delete'.$teacher->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($teacher);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('teacher_index');
    }
}
