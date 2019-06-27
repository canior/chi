<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 13:33
 */

namespace App\Controller\GongZhong;


use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller\GongZhong
 * @author zxqc2018
 */
class UserController extends GongZhongBaseController
{

    /**
     * @Route("/login", name="gzhLogin")
     * @author zxqc2018
     */
    public function login()
    {
        $requestProcess = $this->processRequest(null);
    }
}