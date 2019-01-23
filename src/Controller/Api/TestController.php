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

use App\Repository\FileRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use App\Service\Wx\WxCommon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use App\Entity\Dao;

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
        $course = $this->getEntityManager()->getRepository(Course::class)->find(499);
        echo strtotime(date(DAO::DATETIME_END, $course->getEndDate()));
        echo "<br>";
        echo time();

        return $this->responseRaw("");
    }

}