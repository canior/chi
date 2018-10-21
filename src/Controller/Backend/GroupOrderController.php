<?php

namespace App\Controller\Backend;

use App\Entity\GroupOrder;
use App\Form\GroupOrderType;
use App\Repository\GroupOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class GroupOrderController extends BackendController
{
    /**
     * @Route("/group/order/", name="group_order_index", methods="GET")
     */
    public function index(GroupOrderRepository $groupOrderRepository, Request $request): Response
    {
        $data = [
            'title' => 'GroupOrder 列表',
            'form' => [
                'groupOrderId' => $request->query->getInt('groupOrderId', null),
                'groupUserOrderId' => $request->query->getInt('groupUserOrderId', null),
                'userId' => $request->query->getInt('userId', null),
                'productName' => $request->query->get('productName', null),
                'status' => $request->query->get('status', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'statuses' => GroupOrder::$statuses
        ];
        $data['data'] = $groupOrderRepository->findGroupOrdersQueryBuilder($data['form']['groupOrderId'], $data['form']['groupUserOrderId'], $data['form']['userId'], $data['form']['productName'], $data['form']['status']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/group_order/index.html.twig', $data);
    }

    /**
     * @Route("/group/order/info/{id}", name="group_order_info", methods="GET|POST")
     */
    public function info(Request $request, GroupOrder $groupOrder): Response
    {
        $form = $this->createForm(GroupOrderType::class, $groupOrder);
        $form->get('status')->setData(array_search($groupOrder->getStatusText(), GroupOrder::$statuses));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $status = $request->request->get('group_order')['status'];
            $isMethod = 'is' . ucwords($status);
            if (in_array($status, array_keys(GroupOrder::$statuses))
                && !$groupOrder->$isMethod()) {
                $setMethod = 'set' . ucwords($status);
                $groupOrder->$setMethod();
                $em->persist($groupOrder);
                $em->flush();
            }
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('group_order_info', ['id' => $groupOrder->getId()]);
        }

        return $this->render('backend/group_order/info.html.twig', [
            'group_order' => $groupOrder,
            'title' => 'GroupOrder 详情',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group/order/{id}/edit", name="group_order_edit", methods="GET|POST")
     */
    public function edit(Request $request, GroupOrder $groupOrder): Response
    {
        $form = $this->createForm(GroupOrderType::class, $groupOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('group_order_edit', ['id' => $groupOrder->getId()]);
        }

        return $this->render('backend/group_order/edit.html.twig', [
            'group_order' => $groupOrder,
            'title' => '修改 GroupOrder',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group/order/{id}", name="group_order_delete", methods="DELETE")
     */
    public function delete(Request $request, GroupOrder $groupOrder): Response
    {
        if ($this->isCsrfTokenValid('delete' . $groupOrder->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($groupOrder);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('group_order_index');
    }
}
