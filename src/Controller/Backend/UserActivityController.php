<?php

namespace App\Controller\Backend;

use App\Entity\UserActivity;
use App\Form\UserActivityType;
use App\Repository\UserActivityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class UserActivityController extends BackendController
{
    /**
     * @Route("/user/activity/", name="user_activity_index", methods="GET")
     */
    public function index(UserActivityRepository $userActivityRepository, Request $request): Response
    {
        $data = [
            'title' => 'UserActivity 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $userActivityRepository->findByKeyword($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_activity/index.html.twig', $data);
    }

    /**
     * @Route("/user/activity/new", name="user_activity_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $userActivity = new UserActivity();
        $form = $this->createForm(UserActivityType::class, $userActivity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userActivity);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_activity_index');
        }

        return $this->render('backend/user_activity/new.html.twig', [
            'user_activity' => $userActivity,
            'title' => '添加 UserActivity',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/activity/{id}/edit", name="user_activity_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserActivity $userActivity): Response
    {
        $form = $this->createForm(UserActivityType::class, $userActivity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_activity_edit', ['id' => $userActivity->getId()]);
        }

        return $this->render('backend/user_activity/edit.html.twig', [
            'user_activity' => $userActivity,
            'title' => '修改 UserActivity',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/activity/{id}", name="user_activity_delete", methods="DELETE")
     */
    public function delete(Request $request, UserActivity $userActivity): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userActivity->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userActivity);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('user_activity_index');
    }
}
