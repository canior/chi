<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserAddress;
use App\Repository\UserAddressRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class UserController extends BaseController
{

    /**
     *
     * @param Request $request
     * @return Response
     */
    public function getUsersAction(Request $request) {

        $data = json_decode($request->getContent(), true);

        $userId = isset($data['userId']) ? $data['userId'] : null;
        $skillId = isset($data['skillId']) ? $data['skillId'] : null;
        $gender = isset($data['gender']) ? $data['gender'] : null;
        $regionId = isset($data['regionId']) ? $data['regionId'] : null;
        $skillLevelId = isset($data['skillLevelId']) ? $data['skillLevelId'] : null;
        $page = isset($data['page']) ? $data['page'] : null;
        $pageLimit = isset($data['pageLimit']) ? $data['pageLimit'] : null;

        $usersArray = $this->getDataAccess()->getUsersJson($userId, $regionId, $skillId, $gender, $skillLevelId);

        return $this->responseJson(null, 200, $usersArray);
    }

    public function getUser() {

    }


    /**
     * 获取用户收货地址
     *
     * @Route("/user/addresses/{id}", name="userAddresses", methods="GET")
     * @param Request $request
     * @param User $user
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function userAddressesAction(Request $request, User $user, UserAddressRepository $userAddressRepository): Response {
        $userAddresses = $userAddressRepository->findBy(['user' => $user, 'isDeleted' => false], ['id' => 'DESC']);
        $data = [];
        foreach($userAddresses as $userAddress) {
            $data[] = $userAddress->getArray();
        }
        return $this->responseJson('success', 200, $data);
    }


}