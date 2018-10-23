<?php

namespace App\Controller\Backend;

use App\Entity\UserAddress;
use App\Form\UserAddressType;
use App\Repository\UserAddressRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class UserAddressController extends BackendController
{
    /**
     * @Route("/user/address/", name="user_address_index", methods="GET")
     */
    public function index(UserAddressRepository $userAddressRepository, Request $request): Response
    {
        $data = [
            'title' => 'UserAddress 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $userAddressRepository->findByKeyword($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_address/index.html.twig', $data);
    }

    /**
     * @Route("/user/address/new", name="user_address_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $userAddress = new UserAddress();
        $form = $this->createForm(UserAddressType::class, $userAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userAddress);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('user_address_index');
        }

        return $this->render('backend/user_address/new.html.twig', [
            'user_address' => $userAddress,
            'title' => '添加 UserAddress',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/address/{id}/edit", name="user_address_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserAddress $userAddress): Response
    {
        $form = $this->createForm(UserAddressType::class, $userAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_address_edit', ['id' => $userAddress->getId()]);
        }

        return $this->render('backend/user_address/edit.html.twig', [
            'user_address' => $userAddress,
            'title' => '修改 UserAddress',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/address/{id}", name="user_address_delete", methods="DELETE")
     */
    public function delete(Request $request, UserAddress $userAddress): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userAddress->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userAddress);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('user_address_index');
    }
}
