<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-14
 * Time: 12:33 AM
 */

namespace App\Controller\Api;

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

        echo "test";

        return $this->responseJson('success', 200, []);
    }

}