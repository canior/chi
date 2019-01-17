<?php

namespace App\Controller\Backend;

use App\Entity\UpgradeUserOrder;
use App\Entity\UserLevel;
use App\Form\NewUpgradeUserOrderType;
use App\Repository\UpgradeUserOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EditUpgradeUserOrderType;
use App\Form\VerifyParentUserType;

/**
 * @Route("/backend")
 */
class UpgradeUserOrderController extends BackendController
{
    /**
     * @Route("/upgrade/user/order/", name="upgrade_user_order_index", methods="GET")
     * @param UpgradeUserOrderRepository $upgradeUserOrderRepository
     * @param Request $request
     * @return Response
     */
    public function index(UpgradeUserOrderRepository $upgradeUserOrderRepository, Request $request): Response
    {
        $data = [
            'title' => '会员升级订单管理',
            'form' => [
                'id' => $request->query->get('id', null),
                'userId' => $request->query->get('userId', null),
                'name' => $request->query->get('name', null),
                'oldUserLevel' => $request->query->get('oldUserLevel', null),
                'userLevel' => $request->query->get('userLevel', null),
                'status' => $request->query->get('status', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'userLevels' => UserLevel::$userLevelTextArray,
            'statuses' => UpgradeUserOrder::$statusTexts,

        ];
        $id = $data['form']['id'];
        $userId = $data['form']['userId'];
        $name = $data['form']['name'];
        $oldUserLevel = $data['form']['oldUserLevel'];
        $userLevel = $data['form']['userLevel'];
        $status = $data['form']['status'];

        $data['data'] = $upgradeUserOrderRepository->search($id, $userId, $name, $oldUserLevel, $userLevel, $status);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/upgrade_user_order/index.html.twig', $data);
    }

    /**
     * @Route("/upgrade/user/order/{id}/edit", name="upgrade_user_order_edit", methods="GET|POST")
     * @param Request $request
     * @param UpgradeUserOrder $upgradeUserOrder
     * @return Response
     */
    public function edit(Request $request, UpgradeUserOrder $upgradeUserOrder): Response
    {
        $user = $upgradeUserOrder->getUser();
        $form = $this->createForm(EditUpgradeUserOrderType::class, $upgradeUserOrder);
        $form->get('status')->setData($upgradeUserOrder->getStatus());

        $form->handleRequest($request);

        $verifyParentForm = $this->createForm(VerifyParentUserType::class, $user);
        $verifyParentForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $upgradeUserOrder->setStatus($form->get('status')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('upgrade_user_order_edit', ['id' => $upgradeUserOrder->getId()]);
        }

        if ($verifyParentForm->isSubmitted() && $verifyParentForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('upgrade_user_order_edit', ['id' => $upgradeUserOrder->getId()]);
        }

        return $this->render('backend/upgrade_user_order/edit.html.twig', [
            'upgrade_user_order' => $upgradeUserOrder,
            'title' => '编辑会员升级订单',
            'form' => $form->createView(),
            'user' => $user,
            'verifyParentForm' => $verifyParentForm->createView()
        ]);
    }

}
