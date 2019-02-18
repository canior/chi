<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-18
 * Time: 1:00 PM
 */

namespace App\Controller\Api;

use App\Repository\GroupUserOrderRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class VideoController extends BaseController
{
    /**
     * @Route("/v", name="video", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function videoAction(Request $request)
    {
        echo "this is a test page id = " . $request->query->get('id');
        exit;
    }
}