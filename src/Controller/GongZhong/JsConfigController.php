<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/28
 * Time: 0:57
 */

namespace App\Controller\GongZhong;

use App\Service\Util\FactoryUtil;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class JsConfigController
 * @package App\Controller\GongZhong
 * @author zxqc2018
 */
class JsConfigController extends GongZhongBaseController
{
    /**
     * @Route("/jsConfig", name="gzhJgConfig", methods="POST")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function getJsConfig()
    {
        $requestProcess = $this->processRequest(null, ['url']);
        $url = $requestProcess['url'] ?? '';
        $requestProcess->setData(FactoryUtil::gzhWeChatProcess()->getJsBuildConfig($url));
        return $requestProcess->toJsonResponse();
    }
}