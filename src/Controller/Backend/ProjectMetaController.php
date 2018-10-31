<?php

namespace App\Controller\Backend;

use App\Entity\ProjectMeta;
use App\Form\ProjectMetaType;
use App\Repository\ProjectMetaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProjectMetaController extends BackendController
{
    /**
     * @Route("/project/meta/", name="project_meta_index", methods="GET")
     */
    public function index(ProjectMetaRepository $projectMetaRepository, Request $request): Response
    {
        $data = [
            'title' => '小程序配置',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $projectMetaRepository->findMetas($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/project_meta/index.html.twig', $data);
    }

    /**
     * @Route("/project/meta/new", name="project_meta_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $projectMetum = new ProjectMeta();
        $form = $this->createForm(ProjectMetaType::class, $projectMetum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($projectMetum);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('project_meta_index');
        }

        return $this->render('backend/project_meta/new.html.twig', [
            'project_metum' => $projectMetum,
            'title' => '添加 ProjectMeta',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/project/meta/{id}/edit", name="project_meta_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProjectMeta $projectMetum): Response
    {
        $form = $this->createForm(ProjectMetaType::class, $projectMetum);
        $form->remove('metaKey');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('project_meta_edit', ['id' => $projectMetum->getId()]);
        }

        return $this->render('backend/project_meta/edit.html.twig', [
            'project_metum' => $projectMetum,
            'title' => '修改 ProjectMeta',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/project/meta/{id}", name="project_meta_delete", methods="DELETE")
     */
    public function delete(Request $request, ProjectMeta $projectMetum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projectMetum->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($projectMetum);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('project_meta_index');
    }
}
