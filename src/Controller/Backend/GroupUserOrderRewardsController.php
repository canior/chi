<?php

namespace App\Controller\Backend;

use App\Entity\GroupUserOrderRewards;
use App\Form\GroupUserOrderRewardsType;
use App\Repository\GroupUserOrderRewardsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class GroupUserOrderRewardsController extends BackendController
{
    /**
     * @Route("/group/user/order/rewards/", name="group_user_order_rewards_index", methods="GET")
     */
    public function index(GroupUserOrderRewardsRepository $groupUserOrderRewardsRepository, Request $request): Response
    {
        $data = [
            'title' => 'GroupUserOrderRewards 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $groupUserOrderRewardsRepository->findByKeyword($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/group_user_order_rewards/index.html.twig', $data);
    }

    /**
     * @Route("/group/user/order/rewards/new", name="group_user_order_rewards_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $groupUserOrderReward = new GroupUserOrderRewards();
        $form = $this->createForm(GroupUserOrderRewardsType::class, $groupUserOrderReward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupUserOrderReward);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('group_user_order_rewards_index');
        }

        return $this->render('backend/group_user_order_rewards/new.html.twig', [
            'group_user_order_reward' => $groupUserOrderReward,
            'title' => '添加 GroupUserOrderRewards',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group/user/order/rewards/{id}/edit", name="group_user_order_rewards_edit", methods="GET|POST")
     */
    public function edit(Request $request, GroupUserOrderRewards $groupUserOrderReward): Response
    {
        $form = $this->createForm(GroupUserOrderRewardsType::class, $groupUserOrderReward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('group_user_order_rewards_edit', ['id' => $groupUserOrderReward->getId()]);
        }

        return $this->render('backend/group_user_order_rewards/edit.html.twig', [
            'group_user_order_reward' => $groupUserOrderReward,
            'title' => '修改 GroupUserOrderRewards',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group/user/order/rewards/{id}", name="group_user_order_rewards_delete", methods="DELETE")
     */
    public function delete(Request $request, GroupUserOrderRewards $groupUserOrderReward): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groupUserOrderReward->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($groupUserOrderReward);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('group_user_order_rewards_index');
    }
}
