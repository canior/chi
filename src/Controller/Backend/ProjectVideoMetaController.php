<?php

namespace App\Controller\Backend;


use App\Entity\ProjectVideoMeta;
use App\Form\ProjectVideoMetaType;
use App\Repository\FileRepository;
use App\Repository\ProjectVideoMetaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProjectVideoMetaController extends BackendController
{
    /**
     * @Route("/project/video/meta/", name="project_video_meta_index", methods="GET")
     * @param ProjectVideoMetaRepository $projectVideoMetaRepository
     * @param Request $request
     * @return Response
     */
    public function index(ProjectVideoMetaRepository $projectVideoMetaRepository, Request $request): Response
    {
        $data = [
            'title' => '视频配置',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $projectVideoMetaRepository->findTextMetaQueryBuilder($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/project_video_meta/index.html.twig', $data);
    }

    /**
     * @Route("/project/video/meta/{id}/edit", name="project_video_meta_edit", methods="GET|POST")
     * @param Request $request
     * @param ProjectVideoMeta $projectVideoMeta
     * @param FileRepository $fileRepository
     * @return Response
     */
    public function edit(Request $request, ProjectVideoMeta $projectVideoMeta, FileRepository $fileRepository): Response
    {
        $form = $this->createForm(ProjectVideoMetaType::class, $projectVideoMeta);
        if ($projectVideoMeta->getPreviewImageFileId()) {
            $previewImageFileId = [];
            $file = $fileRepository->find($projectVideoMeta->getPreviewImageFileId());
            $previewImageFileId[] = [
                'id' => $file->getId(),
                'fileId' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize()
            ];
            $form->get('previewImageFileId')->setData($previewImageFileId);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $previewImageFileId = isset($request->request->get('project_video_meta')['previewImageFileId']) ? $request->request->get('project_video_meta')['previewImageFileId'] : null;
            $projectVideoMeta->setPreviewImageFileId($previewImageFileId);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('project_video_meta_edit', ['id' => $projectVideoMeta->getId()]);
        }

        return $this->render('backend/project_video_meta/edit.html.twig', [
            'project_video_metum' => $projectVideoMeta,
            'title' => '修改视频配置',
            'form' => $form->createView(),
        ]);
    }
}
