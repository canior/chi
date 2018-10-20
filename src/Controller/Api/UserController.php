<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserAddress;
use App\Repository\UserAddressRepository;
use App\Repository\UserRepository;
use App\Service\Wx\WxCommon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class UserController extends BaseController
{

    /**
     * 获取用户openId
     *
     * @Route("/user/login", name="userLogin", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function loginAction(Request $request, UserRepository $userRepository) : Response {
        $data = json_decode($request->getContent(), true);
        $code = isset($data['code']) ? $data['code'] : null;
        $thirdSession = isset($data['thirdSession']) ? $data['thirdSession'] : null;
        $nickName = isset($data['nickName']) ? $data['nickName'] : null;
        $avatarUrl = isset($data['avatarUrl']) ? $data['avatarUrl'] : null;
        //$userInfo = isset($data['userInfo']) ? json_decode($data['userInfo'], true) : null;

        $user = null;
        $msg = "";
        if ($thirdSession) {
            $user = $this->getWxUser($thirdSession);
        }

        if ($user != null) {
            $msg = 'has_logined';
        } else {
            $wxApi = new WxCommon($this->getLog());
            $result = $wxApi->getSessionByCode($code);

            if ($result['status']) {
                $openId = $result['data']['openid'];
                $user = $userRepository->findOneBy(['wxOpenId' => $openId]);
                if ($user == null) {
                    $user = new User();
                    $user->setUsername($nickName);
                    $user->setUsernameCanonical($nickName);
                    $user->setEmail($openId . '@qq.com');
                    $user->setEmailCanonical($openId . '@qq.com');
                    $user->setPassword("IamCustomer");
                    $user->setNickname($nickName);
                    $user->setAvatarUrl($avatarUrl);
                    $user->setWxOpenId($openId);
                    $this->getEntityManager()->persist($user);
                    $this->getEntityManager()->flush();

                    $userId = $user->getId();
                    $thirdSession = $userId;//生成我们自己的第三方session
                    $msg = "login_success";
                } else {
                    $msg = "has_logined";
                }
            } else {
                $msg = "login_failed";
            }
        }

        return $this->responseJson($msg, 200, [
            'thirdSession' => $thirdSession,
        ]);

    }


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