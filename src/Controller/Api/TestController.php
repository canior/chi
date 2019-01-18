<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-14
 * Time: 12:33 AM
 */

namespace App\Controller\Api;

use App\Entity\Course;
use App\Entity\CourseOrder;
use App\Entity\Teacher;
use App\Entity\User;
use App\Entity\UpgradeUserOrder;
use App\Entity\ShareSource;

use App\Repository\TeacherRepository;
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
echo $this->getEntityManager()->getRepository(Teacher::class)->find(1)->getName();exit;
        /**
         * @var UpgradeUserOrder $upgradeUserOrder
         */
        $upgradeUserOrder = $this->getEntityManager()->getRepository(UpgradeUserOrder::class)->find(5);
        $userAccountOrders = $upgradeUserOrder->getPotentialUserAccountOrders();
        echo $userAccountOrders->count();
        //var_dump($userAccountOrders[0]->getArray());

        return $this->responseRaw("");
    }

}