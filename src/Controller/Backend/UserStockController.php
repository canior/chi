<?php

namespace App\Controller\Backend;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use App\Entity\User;

/**
 * @Route("/backend")
 */
class UserStockController extends BackendController
{
    /**
     * @Route("/user/stock/statistic", name="user_stock_statistic", methods="GET")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function statistic(UserRepository $userRepository, Request $request): Response
    {
        $data = [
            'title' => '名额管理',
            'form' => [
                'name' => $request->query->get('name', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        if ($data['form']['name']) {
            $data['data'] = $userRepository->findBy(['name' => $data['form']['name']]);
        }
        else {
            $data['data'] = $userRepository->findAll();
        }
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_stock/statistic.html.twig', $data);
    }

    /**
     * @Route("/user/stock/{userId}", name="user_stock_index", methods="GET")
     * @param UserRepository $userRepository
     * @param $userId
     * @param Request $request
     * @return Response
     */
    public function index(UserRepository $userRepository, $userId, Request $request): Response
    {
        $data = [
            'title' => '用户名额管理',
            'form' => [
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        /**
         * @var User $user
         */
        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
        $data['user'] = $user;
        $data['data'] = $user->getUserAccountOrdersAsRecommander();

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_stock/index.html.twig', $data);
    }

}
