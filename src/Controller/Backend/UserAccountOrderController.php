<?php

namespace App\Controller\Backend;

use App\Entity\UserAccountOrder;
use App\Form\EditUserAccountOrderType;
use App\Repository\UserAccountOrderRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\NewUserAccountOrderType;

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
        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
        $data['user'] = $user;
        $data['data'] = $userAccountOrderRepository->findBy(['user' => $userId]);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_account_order/index.html.twig', $data);
    }

    /**
     * @Route("/user/account/order/{userId}/new", name="user_account_order_new", methods="GET|POST")
     * @param Request $request
     * @param $userId
     * @return Response
     */
    public function new(Request $request, $userId): Response
    {
        $userAccountOrder = new UserAccountOrder();

        $form = $this->createForm(NewUserAccountOrderType::class, $userAccountOrder);
        $form->handleRequest($request);

        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
            $userAccountOrder->setUser($user);
            $userAccountOrder->setUserAccountOrderType($form->get('userAccountOrderType')->getData());
            $userAccountOrder->setPaymentStatus($form->get('paymentStatus')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($userAccountOrder);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_account_order_index', ['userId' => $userAccountOrder->getUser()->getId()]);
        }

        return $this->render('backend/user_account_order/new.html.twig', [
            'user' => $user,
            'user_account_order' => $userAccountOrder,
            'title' => '创建交易记录',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/account/order/{id}/edit", name="user_account_order_edit", methods="GET|POST")
     * @param Request $request
     * @param UserAccountOrder $userAccountOrder
     * @return Response
     */
    public function edit(Request $request, UserAccountOrder $userAccountOrder): Response
    {
        $form = $this->createForm(EditUserAccountOrderType::class, $userAccountOrder);
        $form->get('paymentStatus')->setData(array_search($userAccountOrder->getPaymentStatusText(), UserAccountOrder::$paymentStatuses));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userAccountOrder->setPaymentStatus($form->get('paymentStatus')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_account_order_edit', ['id' => $userAccountOrder->getId()]);
        }

        return $this->render('backend/user_account_order/edit.html.twig', [
            'user' => $userAccountOrder->getUser(),
            'user_account_order' => $userAccountOrder,
            'title' => '编辑用户交易账单',
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


    /**
     * @Route("/user/account/withdraw", name="user_account_withdraw_orders", methods="GET")
     * @param Request $request
     * @param UserAccountOrderRepository $userAccountOrderRepository
     * @return Response
     */
    public function withdrawOrderList(Request $request, UserAccountOrderRepository $userAccountOrderRepository) {
        $data = [
            'title' => '提现订单',
            'form' => [
                'page' => $request->query->getInt('page', 1)
            ],
            'data' => [],
        ];
        $data['data'] = $userAccountOrderRepository->findBy(['userAccountOrderType' => UserAccountOrder::WITHDRAW, 'paymentStatus' => UserAccountOrder::UNPAID]);

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_account_order/withdrawOrders.html.twig', $data);
    }
}
