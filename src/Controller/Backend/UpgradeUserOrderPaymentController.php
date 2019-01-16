<?php

namespace App\Controller\Backend;

use App\Entity\UpgradeUserOrder;
use App\Entity\UpgradeUserOrderPayment;
use App\Form\EditUpgradeUserOrderPaymentType;
use App\Repository\UpgradeUserOrderPaymentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\NewUpgradeUserOrderPaymentType;

/**
 * @Route("/backend")
 */
class UpgradeUserOrderPaymentController extends BackendController
{
    /**
     * @Route("/upgrade/user/order/{upgradeUserOrderId}/payment/", name="upgrade_user_order_payment_index", methods="GET")
     * @param $upgradeUserOrderId
     * @param UpgradeUserOrderPaymentRepository $upgradeUserOrderPaymentRepository
     * @param Request $request
     * @return Response
     */
    public function index($upgradeUserOrderId, UpgradeUserOrderPaymentRepository $upgradeUserOrderPaymentRepository, Request $request): Response
    {
        $data = [
            'title' => '支付列表',
            'upgradeUserOrderId' => $upgradeUserOrderId,
        ];
        $data['data'] = $upgradeUserOrderPaymentRepository->findBy(['upgradeUserOrder' => $upgradeUserOrderId]);
        $data['dataCount'] = count($data['data']);
        return $this->render('backend/upgrade_user_order_payment/index.html.twig', $data);
    }

    /**
     * @Route("/upgrade/user/order/{upgradeUserOrderId}/payment/new", name="upgrade_user_order_payment_new", methods="GET|POST")
     * @param Request $request
     * @param $upgradeUserOrderId
     * @return Response
     */
    public function new(Request $request, $upgradeUserOrderId): Response
    {
        $upgradeUserOrderPayment = new UpgradeUserOrderPayment();
        /**
         * @var UpgradeUserOrder $upgradeUserOrder
         */
        $upgradeUserOrder = $this->getEntityManager()->getRepository(UpgradeUserOrder::class)->find($upgradeUserOrderId);
        $upgradeUserOrderPayment->setUpgradeUserOrder($upgradeUserOrder);
        $form = $this->createForm(NewUpgradeUserOrderPaymentType::class, $upgradeUserOrderPayment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($upgradeUserOrderPayment);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('upgrade_user_order_edit', ['id' => $upgradeUserOrderId]);
        }

        return $this->render('backend/upgrade_user_order_payment/new.html.twig', [
            'upgrade_user_order_payment' => $upgradeUserOrderPayment,
            'title' => '添加支付记录',
            'form' => $form->createView(),
            'upgradeUserOrder' => $upgradeUserOrder
        ]);
    }

}
