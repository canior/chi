<?php

namespace App\Controller\Backend;

use App\Entity\ProjectTextMeta;
use App\Form\ProjectTextMetaType;
use App\Repository\ProjectTextMetaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProjectTextMetaController extends BackendController
{
    /**
     * @Route("/project/text/meta/", name="project_text_meta_index", methods="GET")
     */
    public function index(ProjectTextMetaRepository $projectTextMetaRepository, Request $request): Response
    {
        $data = [
            'title' => '文案配置',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $projectTextMetaRepository->findTextMetaQueryBuilder($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/project_text_meta/index.html.twig', $data);
    }

    /**
     * @Route("/project/text/meta/{id}/edit", name="project_text_meta_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProjectTextMeta $projectTextMetum): Response
    {
        $form = $this->createForm(ProjectTextMetaType::class, $projectTextMetum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('project_text_meta_edit', ['id' => $projectTextMetum->getId()]);
        }

        return $this->render('backend/project_text_meta/edit.html.twig', [
            'project_text_metum' => $projectTextMetum,
            'title' => '修改文案配置',
            'form' => $form->createView(),
        ]);
    }
}
