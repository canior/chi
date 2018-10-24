<?php

namespace App\Controller\Backend;

use App\Entity\UserShare;
use App\Form\UserShareType;
use App\Repository\UserShareRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class UserShareController extends BackendController
{
    /**
     * @Route("/user/share/", name="user_share_index", methods="GET")
     */
    public function index(UserShareRepository $userShareRepository, Request $request): Response
    {
        $data = [
            'title' => 'UserShare 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $userShareRepository->findByKeyword($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_share/index.html.twig', $data);
    }

    /**
     * @Route("/user/share/new", name="user_share_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $userShare = new UserShare();
        $form = $this->createForm(UserShareType::class, $userShare);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userShare);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_share_index');
        }

        return $this->render('backend/user_share/new.html.twig', [
            'user_share' => $userShare,
            'title' => '添加 UserShare',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/share/{id}/edit", name="user_share_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserShare $userShare): Response
    {
        $form = $this->createForm(UserShareType::class, $userShare);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_share_edit', ['id' => $userShare->getId()]);
        }

        return $this->render('backend/user_share/edit.html.twig', [
            'user_share' => $userShare,
            'title' => '修改 UserShare',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/share/{id}", name="user_share_delete", methods="DELETE")
     */
    public function delete(Request $request, UserShare $userShare): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userShare->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userShare);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('user_share_index');
    }
}
