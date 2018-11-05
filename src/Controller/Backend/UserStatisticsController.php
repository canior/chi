<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Entity\UserStatistics;
use App\Form\UserStatisticsType;
use App\Repository\GroupUserOrderRewardsRepository;
use App\Repository\UserStatisticsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class UserStatisticsController extends BackendController
{
    /**
     * @Route("/user/statistics/", name="user_statistics_index", methods="GET")
     */
    public function index(UserStatisticsRepository $userStatisticsRepository, Request $request): Response
    {
        $data = [
            'title' => '用户收益',
            'form' => [
                'userId' => $request->query->getInt('userId', null),
                'username' => $request->query->get('username', null),
                'year' => $request->query->getInt('year', null),
                'month' => $request->query->getInt('month', null),
                'day' => $request->query->getInt('day', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'yearStart' => 2018,
            'yearEnd' => date('Y')
        ];
        $data['data'] = $userStatisticsRepository->findUserStatisticsQueryBuilder($data['form']['userId'], $data['form']['username'], $data['form']['year'], $data['form']['month'], $data['form']['day']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_statistics/index.html.twig', $data);
    }

    /**
     * @Route("/user/statistics/info/{id}", name="user_statistics_info", methods="GET")
     */
    public function info(Request $request, User $user, UserStatisticsRepository $userStatisticsRepository, GroupUserOrderRewardsRepository $groupUserOrderRewardsRepository): Response
    {
        $queryBuilder = $userStatisticsRepository->findUserStatisticsQueryBuilder($user->getId());
        $userStatisticsTotal = $queryBuilder->getQuery()->getOneOrNullResult();
        $parentUserStatisticsTotal = null;
        if ($user->getParentUser()) {
            $queryBuilder = $userStatisticsRepository->findUserStatisticsQueryBuilder($user->getParentUser()->getId());
            $parentUserStatisticsTotal = $queryBuilder->getQuery()->getOneOrNullResult();
        }
        $data = [
            'title' => '收益详情',
            'user' => $user,
            'userStatisticsTotal' => $userStatisticsTotal,
            'parentUserStatisticsTotal' => $parentUserStatisticsTotal,
            'subUsers' => $groupUserOrderRewardsRepository->findSubUsers($user->getId()),
        ];
        return $this->render('backend/user_statistics/info.html.twig', $data);
    }

    /**
     * @Route("/user/statistics/new", name="user_statistics_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $userStatistic = new UserStatistics();
        $form = $this->createForm(UserStatisticsType::class, $userStatistic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userStatistic);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_statistics_index');
        }

        return $this->render('backend/user_statistics/new.html.twig', [
            'user_statistic' => $userStatistic,
            'title' => '添加 UserStatistics',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/statistics/{id}/edit", name="user_statistics_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserStatistics $userStatistic): Response
    {
        $form = $this->createForm(UserStatisticsType::class, $userStatistic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_statistics_edit', ['id' => $userStatistic->getId()]);
        }

        return $this->render('backend/user_statistics/edit.html.twig', [
            'user_statistic' => $userStatistic,
            'title' => '修改 UserStatistics',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/statistics/{id}", name="user_statistics_delete", methods="DELETE")
     */
    public function delete(Request $request, UserStatistics $userStatistic): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userStatistic->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userStatistic);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('user_statistics_index');
    }
}
