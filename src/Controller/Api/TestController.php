<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-14
 * Time: 12:33 AM
 */

namespace App\Controller\Api;

use App\Entity\Course;
use App\Entity\Teacher;
use App\Entity\User;
use App\Entity\UpgradeUserOrder;
use App\Entity\ShareSource;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class TestController extends BaseController
{
    /**
     * @Route("/test", name="test", methods="GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request) {
        if ($this->getEnvironment() != 'dev') exit;

        /**
         * @var UserRepository $userRepository
         */
        $userRepository = $this->getEntityManager()->getRepository(User::class);

        echo $userRepository->findTotalShareUsers(1, 'PARTNER');

        return $this->responseRaw("");
    }

}