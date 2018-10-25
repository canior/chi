<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-08-27
 * Time: 6:14 PM
 */

namespace App\Controller\Api;

use App\Controller\DefaultController;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BaseController extends DefaultController
{
    protected function response403($msg = 'no_auth', $data = [])
    {
        return $this->responseJson($msg, 403, $data);
    }

    protected function response404($msg = 'not_found', $data = [])
    {
        return $this->responseJson($msg, 404, $data);
    }

    protected function response503($msg = 'there_is_an_error', $data = [])
    {
        return $this->responseJson($msg, 503, $data);
    }

    protected function responseJson($msg = null, $code = null, $data = null)
    {
        return $this->json(compact('msg', 'code', 'data'));
    }

    protected function responseRaw($content)
    {
        return new Response($content);
    }

    protected function responseNeedLogin($msg = null)
    {
        $msg = $msg ? $msg : 'need_login';
        return $this->responseJson($msg, 403, []);
    }

    /**
     * @param $thirdSession ex. {"userId": 123, "shareSourceId": 456}
     * @return User|null
     */
    protected function getWxUser($thirdSession):?User
    {
        return $this->getEntityManager()->getRepository(User::class)->find($thirdSession);


//        $thirdSessionArray = json_decode($thirdSession, true);
//        $userId = $thirdSession['userId'];
//        /**
//         * @var UserRepository $userRepository
//         */
//        $userRepository = $this->getEntityManager()->getRepository(User::class);
//        $user = $userRepository->find($thirdSession);
//
//        $shareSourceId = $thirdSession['shareSourceId'];
//        /**
//         * @var ShareSource $shareSource
//         */
//        $shareSource = $this->getEntityManager()->getRepository(ShareSource::class)->find($shareSourceId);
//
//        if ($shareSource != null) {
//            $shareSourceUser = new ShareSourceUser();
//            $shareSourceUser->setUser($user);
//            $shareSourceUser->setShareSource($shareSource);
//            $shareSource->addShareSourceUser($shareSourceUser);
//            $this->getEntityManager()->persist($shareSource);
//            $this->getEntityManager()->flush();
//        }
//
//        return $user;
    }

    protected function getImgUrlPrefix()
    {
        return $this->generateUrl('imagePreview', ['fileId' => null], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}