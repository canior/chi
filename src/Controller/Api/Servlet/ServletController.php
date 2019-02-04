<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-04
 * Time: 12:00 PM
 */

namespace App\Controller\Api\Servlet;


use App\Controller\Api\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServletController extends BaseController
{
    /**
     * @param Request $request
     * @return bool
     */
    public function validateRequest(Request $request) : bool {
        return true;
    }
}