<?php

namespace App\Controller\Backend;

use App\Entity\ProjectShareMeta;
use App\Entity\ShareSource;
use App\Form\ProjectShareMetaType;
use App\Repository\FileRepository;
use App\Repository\ProjectShareMetaRepository;
use App\Repository\ShareSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;

/**
 * @Route("/backend")
 */
class ProjectShareMetaController extends BackendController
{
    /**
     * @Route("/project/share/meta/", name="project_share_meta_index", methods="GET")
     */
    public function index(ProjectShareMetaRepository $projectShareMetaRepository, Request $request): Response
    {
        $data = [
            'title' => '分享配置',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $projectShareMetaRepository->findShareMetaQueryBuilder($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/project_share_meta/index.html.twig', $data);
    }

    /**
     * @Route("/project/share/meta/{id}/edit", name="project_share_meta_edit", methods="GET|POST")
     */
    public function edit(Request $request, ProjectShareMeta $projectShareMetum, FileRepository $fileRepository): Response
    {
        $form = $this->createForm(ProjectShareMetaType::class, $projectShareMetum);

        // init shareBannerFileId
        $shareSources = [];
        if ($projectShareMetum->isBannerEditable() && $projectShareMetum->getShareBannerFileId()) {
            $shareBannerFileId = [];
            $file = $fileRepository->find($projectShareMetum->getShareBannerFileId());
            $shareBannerFileId[] = [
                'id' => $file->getId(),
                'fileId' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize()
            ];
            $form->get('shareBannerFileId')->setData($shareBannerFileId);

            $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);
            /**
             * @var ShareSource[] $shareSources
             */
            $shareSources = $shareSourceRepository->findBy(['bannerFile' => $projectShareMetum->getShareBannerFileId()]);

            foreach($shareSources as $shareSource) {
                $shareSources[] = $shareSource;
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($projectShareMetum->isBannerEditable()) {
                $fileRepository = $this->getEntityManager()->getRepository(File::class);
                $shareBannerFileId = isset($request->request->get('project_share_meta')['shareBannerFileId']) ? $request->request->get('project_share_meta')['shareBannerFileId'] : null;
                $projectShareMetum->setShareBannerFileId($shareBannerFileId);

                //更新相关的share source
                foreach ($shareSources as $shareSource) {
                    /**
                     * @var File $file
                     */
                    $file = $fileRepository->find($shareBannerFileId);
                    $shareSource->setBannerFile($file);
                    $this->getEntityManager()->persist($shareSource);
                }

            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('project_share_meta_edit', ['id' => $projectShareMetum->getId()]);
        }

        return $this->render('backend/project_share_meta/edit.html.twig', [
            'project_share_metum' => $projectShareMetum,
            'title' => '修改分享配置',
            'form' => $form->createView(),
        ]);
    }
}
