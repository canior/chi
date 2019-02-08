<?php

namespace App\Controller\Backend;

use App\Entity\CourseOrder;
use App\Entity\GroupUserOrder;
use App\Form\EditGroupUserOrderType;
use App\Form\GroupUserOrderType;
use App\Form\VerifyParentUserType;
use App\Repository\GroupUserOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class GroupUserOrderController extends BackendController
{
    /**
     * @Route("/group/user/order/", name="group_user_order_index", methods="GET")
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param Request $request
     * @return Response
     */
    public function index(GroupUserOrderRepository $groupUserOrderRepository, Request $request): Response
    {
        $data = [
            'title' => '产品订单',
            'form' => [
                'groupOrderId' => $request->query->getInt('groupOrderId', null),
                'groupUserOrderId' => $request->query->getInt('groupUserOrderId', null),
                'userId' => $request->query->getInt('userId', null),
                'productName' => $request->query->get('productName', null),
                'type' => $request->query->get('type', null),
                'status' => $request->query->get('status', null),
                'paymentStatus' => $request->query->get('paymentStatus', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'statuses' => GroupUserOrder::$statuses,
            'paymentStatuses' => GroupUserOrder::$paymentStatuses,
        ];
        $data['data'] = $groupUserOrderRepository->findGroupUserOrdersQueryBuilder(false, $data['form']['groupOrderId'], $data['form']['groupUserOrderId'], $data['form']['userId'], $data['form']['productName'], $data['form']['type'], $data['form']['status'], $data['form']['paymentStatus']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/group_user_order/index.html.twig', $data);
    }

    /**
     * @Route("/group/user/order/info/{id}", name="group_user_order_info", methods="GET|POST")
     */
    public function info(Request $request, GroupUserOrder $groupUserOrder): Response
    {
        $form = $this->createForm(GroupUserOrderType::class, $groupUserOrder);
        $form->get('status')->setData(array_search($groupUserOrder->getStatusText(), GroupUserOrder::$statuses));
        $form->get('paymentStatus')->setData(array_search($groupUserOrder->getPaymentStatusText(), GroupUserOrder::$paymentStatuses));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $request->request->get('group_user_order')['status'];
            $paymentStatus = $request->request->get('group_user_order')['paymentStatus'];
            $isStatusMethod = 'is' . ucwords($status);
            if (in_array($status, array_keys(GroupUserOrder::$statuses))
                && !$groupUserOrder->$isStatusMethod()) {
                $setStatusMethod = 'set' . ucwords($status);
                $groupUserOrder->$setStatusMethod();
            }
            $isPaymentStatusMethod = 'is' . ucwords($paymentStatus);
            if (in_array($status, array_keys(GroupUserOrder::$paymentStatuses))
                && !$groupUserOrder->$isPaymentStatusMethod()) {
                $setPaymentMethod = 'set' . ucwords($paymentStatus);
                $groupUserOrder->$setPaymentMethod();
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupUserOrder);
            $em->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('group_user_order_info', ['id' => $groupUserOrder->getId()]);
        }

        return $this->render('backend/group_user_order/info.html.twig', [
            'group_user_order' => $groupUserOrder,
            'title' => '产品订单',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group/user/order/new", name="group_user_order_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $groupUserOrder = new GroupUserOrder();
        $form = $this->createForm(GroupUserOrderType::class, $groupUserOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupUserOrder);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('group_user_order_index');
        }

        return $this->render('backend/group_user_order/new.html.twig', [
            'group_user_order' => $groupUserOrder,
            'title' => '添加 GroupUserOrder',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group/user/order/{id}/edit", name="group_user_order_edit", methods="GET|POST")
     * @param Request $request
     * @param CourseOrder $groupUserOrder
     * @return Response
     */
    public function edit(Request $request, CourseOrder $groupUserOrder): Response
    {
        $form = $this->createForm(EditGroupUserOrderType::class, $groupUserOrder);
        $form->get('status')->setData(array_search($groupUserOrder->getCourseStatusText(), GroupUserOrder::$courseStatuses));
        $form->get('paymentStatus')->setData(array_search($groupUserOrder->getPaymentStatusText(), GroupUserOrder::$paymentStatuses));
        $form->handleRequest($request);

        $user = $groupUserOrder->getUser();
        $verifyParentForm = $this->createForm(VerifyParentUserType::class, $user);
        $verifyParentForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupUserOrder->setStatus($form->get('status')->getData());
            $groupUserOrder->setPaymentStatus($form->get('paymentStatus')->getData());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('group_user_order_edit', ['id' => $groupUserOrder->getId()]);
        }

        if ($verifyParentForm->isSubmitted() && $verifyParentForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('group_user_order_edit', ['id' => $groupUserOrder->getId()]);
        }

        return $this->render('backend/group_user_order/edit.html.twig', [
            'group_user_order' => $groupUserOrder,
            'title' => '编辑课程订单',
            'form' => $form->createView(),
            'verifyParentForm' => $verifyParentForm->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/group/user/order/{id}", name="group_user_order_delete", methods="DELETE")
     */
    public function delete(Request $request, GroupUserOrder $groupUserOrder): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groupUserOrder->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($groupUserOrder);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('group_user_order_index');
    }
}
