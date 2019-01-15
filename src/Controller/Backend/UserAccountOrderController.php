<?php

namespace App\Controller\Backend;

use App\Entity\UserAccountOrder;
use App\Form\UserAccountOrderType;
use App\Repository\UserAccountOrderRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class UserAccountOrderController extends BackendController
{
    /**
     * @Route("/user/account/order/statistic", name="user_account_order_statistic", methods="GET")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function statistic(UserRepository $userRepository, Request $request): Response
    {
        $data = [
            'title' => '账户管理',
            'form' => [
                'name' => $request->query->get('name', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        if ($data['form']['name'])
            $data['data'] = $userRepository->findBy(['name' => $data['form']['name']]);
        else
            $data['data'] = $userRepository->findAll();

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_account_order/statistic.html.twig', $data);
    }

    /**
     * @Route("/user/account/order/{userId}", name="user_account_order_index", methods="GET")
     * @param UserAccountOrderRepository $userAccountOrderRepository
     * @param $userId
     * @param Request $request
     * @return Response
     */
    public function index(UserAccountOrderRepository $userAccountOrderRepository, $userId, Request $request): Response
    {
        $data = [
            'title' => '账户管理',
            'form' => [
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $userAccountOrderRepository->findBy(['user' => $userId]);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_account_order/index.html.twig', $data);
    }

    /**
     * @Route("/user/account/order/new", name="user_account_order_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $userAccountOrder = new UserAccountOrder();
        $form = $this->createForm(UserAccountOrderType::class, $userAccountOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userAccountOrder);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_account_order_index');
        }

        return $this->render('backend/user_account_order/new.html.twig', [
            'user_account_order' => $userAccountOrder,
            'title' => '添加 UserAccountOrder',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/account/order/{id}/edit", name="user_account_order_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserAccountOrder $userAccountOrder): Response
    {
        $form = $this->createForm(UserAccountOrderType::class, $userAccountOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_account_order_edit', ['id' => $userAccountOrder->getId()]);
        }

        return $this->render('backend/user_account_order/edit.html.twig', [
            'user_account_order' => $userAccountOrder,
            'title' => '修改 UserAccountOrder',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/account/order/{id}", name="user_account_order_delete", methods="DELETE")
     */
    public function delete(Request $request, UserAccountOrder $userAccountOrder): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userAccountOrder->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userAccountOrder);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('user_account_order_index');
    }
}
