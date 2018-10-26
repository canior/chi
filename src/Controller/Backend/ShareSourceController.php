<?php

namespace App\Controller\Backend;

use App\Entity\ShareSource;
use App\Entity\User;
use App\Form\ShareSourceType;
use App\Repository\ShareSourceRepository;
use App\Repository\ShareSourceUserRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ShareSourceController extends BackendController
{
    /**
     * @Route("/share/source/", name="share_source_index", methods="GET")
     */
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $data = [
            'title' => 'ShareSource 列表',
            'form' => [
                'userId' => $request->query->get('userId', null),
                'username' => $request->query->get('username', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $userRepository->findUsersQueryBuilder($data['form']['userId'], $data['form']['username']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/share_source/index.html.twig', $data);
    }

    /**
     * @Route("/share/source/info/{id}", name="share_source_info", methods="GET")
     */
    public function info(Request $request, User $user, ShareSourceUserRepository $shareSourceUserRepository, ShareSourceRepository $shareSourceRepository): Response
    {
        $data = [
            'title' => 'ShareSource 详情',
            'user' => $user,
            'underlingUsers' => $shareSourceUserRepository->findUnderlingUsers($user->getId()),
            'shareSources' => $shareSourceRepository->findShareSources($user->getId())
        ];

        return $this->render('backend/share_source/info.html.twig', $data);
    }

    /**
     * @Route("/share/source/new", name="share_source_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $shareSource = new ShareSource();
        $form = $this->createForm(ShareSourceType::class, $shareSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shareSource);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('share_source_index');
        }

        return $this->render('backend/share_source/new.html.twig', [
            'share_source' => $shareSource,
            'title' => '添加 ShareSource',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/share/source/{id}/edit", name="share_source_edit", methods="GET|POST")
     */
    public function edit(Request $request, ShareSource $shareSource): Response
    {
        $form = $this->createForm(ShareSourceType::class, $shareSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('share_source_edit', ['id' => $shareSource->getId()]);
        }

        return $this->render('backend/share_source/edit.html.twig', [
            'share_source' => $shareSource,
            'title' => '修改 ShareSource',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/share/source/{id}", name="share_source_delete", methods="DELETE")
     */
    public function delete(Request $request, ShareSource $shareSource): Response
    {
        if ($this->isCsrfTokenValid('delete' . $shareSource->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shareSource);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('share_source_index');
    }
}
