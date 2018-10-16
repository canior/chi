<?php
namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-08-27
 * Time: 6:12 PM
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

}