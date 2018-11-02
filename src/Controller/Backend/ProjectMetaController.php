<?php

namespace App\Controller\Backend;

use App\Entity\ProjectMeta;
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
