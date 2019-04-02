<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Entity\UserLevel;
use App\Form\UserRoleType;
use App\Form\UserType;
use App\Form\VerifyParentUserType;
use App\Form\VerifyPartnerTeacherType;
use App\Repository\ProductReviewRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatisticsRepository;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserPersonalType;

/**
 * @Route("/backend")
 */
class UserController extends BackendController
{
    /**
     * @Route("/user/", name="user_index", methods="GET")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $data = [
            'title' => '用户管理',
            'form' => [
                'userId' => $request->query->getInt('userId', null),
                'username' => $request->query->get('username', null),
                'userLevel' => $request->query->get('userLevel', null),
                'role' => $request->query->get('role', null),
                'createdAtStart' => $request->query->get('createdAtStart', null),
                'createdAtEnd' => $request->query->get('createdAtEnd', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'roles' => User::$roleTexts,
            'userLevels' => UserLevel::$userLevelTextArray,
        ];
        $data['data'] = $userRepository->findUsersQueryBuilder($data['form']['userId'], $data['form']['username'], $data['form']['role'], $data['form']['userLevel'], $data['form']['createdAtStart'], $data['form']['createdAtEnd']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user/index.html.twig', $data);
    }

    /**
     * @Route("/user/info/{id}", name="user_info", methods="GET|POST")
     */
    public function info(Request $request, User $user, UserManagerInterface $userManager, ProductReviewRepository $productReviewRepository, UserStatisticsRepository $userStatisticsRepository): Response
    {
        $form = $this->createForm(UserRoleType::class, $user);
        $form->get('roles')->setData($user->getRoles());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $request->request->get('user_role')['roles'];
            $user->setRoles($roles);
            $userManager->updateUser($user);

            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_info', ['id' => $user->getId()]);
        }

        return $this->render('backend/user/info.html.twig', [
            'user' => $user,
            'userStatisticsTotal' => $userStatisticsRepository->findUserStatisticsQueryBuilder($user->getId())->getQuery()->getOneOrNullResult(),
            'title' => '个人资料',
            'form' => $form->createView(),
            'productReviews' => $productReviewRepository->findUserProductReviews($user->getId(), 1, 5),
            'productReviewsTotal' => $productReviewRepository->findUserProductReviewsTotal($user->getId())
        ]);
    }



    /**
     * @Route("/user/personal/{id}/edit", name="user_personal_edit", methods="GET|POST")
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function editUserPersonal(Request $request, User $user) {
        $form = $this->createForm(UserPersonalType::class, $user);
        $form->get('userLevel')->setData(array_search($user->getUserLevelText(), UserLevel::$userLevelTextArray));
        $form->get('bianxianUserLevel')->setData(array_search($user->getUserLevelText(), BianxianUserLevel::$userLevelTextArray));
        $form->handleRequest($request);

        $verifyParentForm = $this->createForm(VerifyParentUserType::class, $user);
        $verifyParentForm->handleRequest($request);

        $verifyPartnerTeacherForm = $this->createForm(VerifyPartnerTeacherType::class, $user);
        $verifyPartnerTeacherForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setUserLevel($form->get('userLevel')->getData());
            $user->setBianxianUserLevel($form->get('bianxianUserLevel')->getData());
            $this->getEntityManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_personal_edit', ['id' => $user->getId()]);
        }

        if ($verifyParentForm->isSubmitted() && $verifyParentForm->isValid()) {
            $this->getEntityManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_personal_edit', ['id' => $user->getId()]);
        }

        if ($verifyPartnerTeacherForm->isSubmitted() && $verifyPartnerTeacherForm->isValid()) {
            $this->getEntityManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_personal_edit', ['id' => $user->getId()]);
        }

        return $this->render('backend/user/personal.edit.html.twig', [
            'user' => $user,
            'title' => '用户详情',
            'form' => $form->createView(),
            'verifyParentForm' => $verifyParentForm->createView(),
            'verifyPartnerTeacherForm' => $verifyPartnerTeacherForm->createView()
        ]);
    }

    /**
     * @Route("/user/parent/{id}/edit", name="user_parent_edit", methods="GET|POST")
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function editParentUser(Request $request, User $user) {
        $form = $this->createForm(VerifyParentUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_personal_edit', ['id' => $user->getId()]);
        }

        return $this->render('backend/user/_form.verifyParent.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/user/partnerTeacher/{id}/edit", name="user_partner_teacher_edit", methods="GET|POST")
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function editPartnerTeacherUser(Request $request, User $user) {
        $form = $this->createForm(VerifyPartnerTeacherType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_personal_edit', ['id' => $user->getId()]);
        }

        return $this->render('backend/user/_form.verifyPartnerTeacher.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/new", name="user_new", methods="GET|POST")
     */
    public function new(Request $request, UserManagerInterface $userManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->get('roles')->setData($user->getRoles());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $request->request->get('user')['roles'];
            $user->setRoles($roles);
            if (empty($user->getEmail())) {
                $user->setEmail($user->getUsername() . '@test.com');
            }
            if ($request->request->get('user')['password']) {
                $user->setPlainPassword($request->request->get('user')['password']);
                $userManager->updatePassword($user);
            }
            $user->setEnabled($form->get('enabled')->getData());
            $userManager->updateUser($user);
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('backend/user/new.html.twig', [
            'user' => $user,
            'title' => '添加后台用户',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit", methods="GET|POST")
     * @param Request $request
     * @param User $user
     * @param UserManagerInterface $userManager
     * @return Response
     */
    public function edit(Request $request, User $user, UserManagerInterface $userManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->get('roles')->setData($user->getRoles());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $request->request->get('user')['roles'];
            $user->setRoles($roles);
            if ($request->request->get('user')['password']) {
                $user->setPlainPassword($request->request->get('user')['password']);
                $userManager->updatePassword($user);
            }
            $user->setEnabled($form->get('enabled')->getData());
            $userManager->updateUser($user);
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('backend/user/edit.html.twig', [
            'user' => $user,
            'title' => '修改后台用户',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}", name="user_delete", methods="DELETE")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('user_index');
    }
}
