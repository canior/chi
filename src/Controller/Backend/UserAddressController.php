<?php

namespace App\Controller\Backend;

use App\Entity\UserAddress;
use App\Form\UserAddressType;
use App\Repository\RegionRepository;
use App\Repository\UserAddressRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

/**
 * @Route("/backend")
 */
class UserAddressController extends BackendController
{
    /**
     * @Route("/user/address/{userId}", name="user_address_index", methods="GET")
     * @param UserAddressRepository $userAddressRepository
     * @param $userId
     * @param Request $request
     * @return Response
     */
    public function index(UserAddressRepository $userAddressRepository, $userId, Request $request): Response
    {
        $data = [
            'title' => '地址列表',
            'userId' => $userId,
        ];
        $data['data'] = $userAddressRepository->findBy(['user' => $userId]);
        $data['dataCount'] = count($data['data']);
        return $this->render('backend/user_address/index.html.twig', $data);
    }

    /**
     * @Route("/user/address/{userId}/new", name="user_address_new", methods="GET|POST")
     * @param Request $request
     * @param UserAddressRepository $userAddressRepository
     * @param $userId
     * @return Response
     */
    public function new(Request $request, UserAddressRepository $userAddressRepository, $userId): Response
    {

        /**
         * @var User $user
         */
        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
        $userAddress = new UserAddress();
        $userAddress->setUser($user);

        $form = $this->createForm(UserAddressType::class, $userAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($userAddress);
            $em->flush();

            $this->addFlash('notice', '创建成功');
            return $this->redirectToRoute('user_personal_edit', ['id' => $userId]);
        }

        return $this->render('backend/user_address/new.html.twig', [
            'title' => '创建新地址',
            'form' => $form->createView(),
            'userId' => $userId,
        ]);
    }


    /**
     * @Route("/user/address/{id}/edit", name="user_address_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserAddress $userAddress, RegionRepository $regionRepository): Response
    {
        $form = $this->createForm(UserAddressType::class, $userAddress);

        // init regions
        $form->get('provinceId')->setData($userAddress->getRegion() && $userAddress->getRegion()->getProvince() ? $userAddress->getRegion()->getProvince()->getId() : null);
        $form->get('cityId')->setData($userAddress->getRegion() && $userAddress->getRegion()->getCity() ? $userAddress->getRegion()->getCity()->getId() : null);
        $form->get('countyId')->setData($userAddress->getRegion() && $userAddress->getRegion()->getCounty() ? $userAddress->getRegion()->getCounty()->getId() : null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $provinceId = $form->get('provinceId')->getData();
            $cityId = $form->get('cityId')->getData();
            $countyId = $form->get('countyId')->getData();
            $regionId = $countyId ? $countyId : ($cityId ? $cityId : $provinceId);
            if ($regionId) {
                $region = $regionRepository->find($regionId);
                $userAddress->setRegion($region);
            }
            // reset other default address
            if ($userAddress->getIsDefault()) {
                foreach ($userAddress->getUser()->getUserAddresses() as $address) {
                    if ($address->getIsDefault() && $address->getId() != $userAddress->getId()) {
                        $address->setIsDefault(false);
                    }
                }
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('user_info', ['id' => $userAddress->getUser()->getId()]);
        }

        return $this->render('backend/user_address/edit.html.twig', [
            'user_address' => $userAddress,
            'title' => '修改收货地址',
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
