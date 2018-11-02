<?php

namespace App\Controller\Backend;

use App\Entity\ProjectBannerMeta;
use App\Form\ProjectBannerMetaType;
use App\Repository\FileRepository;
use App\Repository\ProjectBannerMetaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProjectBannerMetaController extends BackendController
{
    /**
     * @Route("/project/banner/meta/", name="project_banner_meta_index", methods="GET")
     */
    public function index(ProjectBannerMetaRepository $projectBannerMetaRepository, Request $request): Response
    {
        $data = [
            'title' => '横幅配置',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $projectBannerMetaRepository->findBannerMetaQueryBuilder($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/project_banner_meta/index.html.twig', $data);
    }

    /**
     * @Route("/project/banner/meta/{id}/edit", name="project_banner_meta_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProjectBannerMeta $projectBannerMetum, FileRepository $fileRepository): Response
    {
        $form = $this->createForm(ProjectBannerMetaType::class, $projectBannerMetum);

        // init images
        if ($projectBannerMetum->getBannerFileId()) {
            $bannerFileId = [];
            $file = $fileRepository->find($projectBannerMetum->getBannerFileId());
            $bannerFileId[] = [
                'id' => $file->getId(),
                'fileId' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize()
            ];
            $form->get('bannerFileId')->setData($bannerFileId);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bannerFileId = isset($request->request->get('project_banner_meta')['bannerFileId']) ? $request->request->get('project_banner_meta')['bannerFileId'] : null;
            $projectBannerMetum->setBannerFileId($bannerFileId);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('project_banner_meta_edit', ['id' => $projectBannerMetum->getId()]);
        }

        return $this->render('backend/project_banner_meta/edit.html.twig', [
            'project_banner_metum' => $projectBannerMetum,
            'title' => '修改横幅配置',
            'form' => $form->createView(),
        ]);
    }
}
