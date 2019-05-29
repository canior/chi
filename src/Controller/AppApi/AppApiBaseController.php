<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 11:14
 */

namespace App\Controller\AppApi;


use App\Controller\Api\BaseController;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AppApiBaseController extends BaseController
{
    /**
     * 获取app登陆用户
     * @return User|object|string|null
     */
    protected function getAppUser()
    {
        $res = null;
        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $this->get('security.token_storage');

        if (empty($tokenStorage->getToken())) {
            return $res;
        }

        if (is_string($tokenStorage->getToken()->getUser()) && $tokenStorage->getToken()->getUser() === 'anon.') {
            return $res;
        }

        return $tokenStorage->getToken()->getUser();
    }
}