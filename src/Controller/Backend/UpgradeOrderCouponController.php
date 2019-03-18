<?php

namespace App\Controller\Backend;

use App\Entity\UpgradeOrderCoupon;
use App\Entity\UserLevel;
use App\Form\UpgradeOrderCouponType;
use App\Repository\UpgradeOrderCouponRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class UpgradeOrderCouponController extends BackendController
{
    /**
     * @Route("/upgrade/order/coupon/", name="upgrade_order_coupon_index", methods="GET")
     * @param UpgradeOrderCouponRepository $upgradeOrderCouponRepository
     * @param Request $request
     * @return Response
     */
    public function index(UpgradeOrderCouponRepository $upgradeOrderCouponRepository, Request $request): Response
    {
        $data = [
            'title' => '升级码管理',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        if ($data['form']['keyword']) {
            $data['data'] = $upgradeOrderCouponRepository->findBy(['coupon' => $data['form']['keyword']]);
        } else {
            $data['data'] = $upgradeOrderCouponRepository->findAll();
        }

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/upgrade_order_coupon/index.html.twig', $data);
    }

    /**
     * @Route("/upgrade/order/coupon/{id}/edit", name="upgrade_order_coupon_edit", methods="GET|POST")
     */
    public function edit(Request $request, UpgradeOrderCoupon $upgradeOrderCoupon): Response
    {
        $form = $this->createForm(UpgradeOrderCouponType::class, $upgradeOrderCoupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $upgradeOrderCoupon->getUpgradeUser()->setUserLevel(UserLevel::ADVANCED);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('upgrade_order_coupon_edit', ['id' => $upgradeOrderCoupon->getId()]);
        }

        return $this->render('backend/upgrade_order_coupon/edit.html.twig', [
            'upgrade_order_coupon' => $upgradeOrderCoupon,
            'title' => '修改 UpgradeOrderCoupon',
            'form' => $form->createView(),
        ]);
    }

}
