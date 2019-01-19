<?php

namespace App\Controller\Backend;

use App\Entity\UserRecommandStockOrder;
use App\Form\UserRecommandStockOrderType;
use App\Repository\UserRecommandStockOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

/**
 * @Route("/backend")
 */
class UserRecommandStockOrderController extends BackendController
{
    /**
     * @Route("/user/recommand/stock/order/{userId}", name="user_recommand_stock_order_index", methods="GET")
     * @param UserRecommandStockOrderRepository $userRecommandStockOrderRepository
     * @param Request $request
     * @return Response
     */
    public function index(UserRecommandStockOrderRepository $userRecommandStockOrderRepository, Request $request, $userId): Response
    {
        $data = [
            'title' => '',
            'userId' => $userId,
        ];
        $data['data'] = $userRecommandStockOrderRepository->findBy(['user' => $userId]);
        $data['dataTotal'] = count($data['data']);
        return $this->render('backend/user_recommand_stock_order/index.html.twig', $data);
    }

    /**
     * @Route("/user/recommand/stock/order/{userId}/new", name="user_recommand_stock_order_new", methods="GET|POST")
     */
    public function new(Request $request, $userId): Response
    {
        $userRecommandStockOrder = new UserRecommandStockOrder();
        /**
         * @var User $user
         */
        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);

        $form = $this->createForm(UserRecommandStockOrderType::class, $userRecommandStockOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->createUserRecommandStockOrder($userRecommandStockOrder->getQty(), $userRecommandStockOrder->getUpgradeUserOrder(), $userRecommandStockOrder->getMemo());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_stock_index' , ['userId' => $userId]);
        }

        return $this->render('backend/user_recommand_stock_order/new.html.twig', [
            'user_recommand_stock_order' => $userRecommandStockOrder,
            'title' => '名额管理',
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}
