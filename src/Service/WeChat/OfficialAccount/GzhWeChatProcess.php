<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/28
 * Time: 00:37
 */

namespace App\Service\WeChat\OfficialAccount;

use App\Service\Config\ConfigParams;

/**
 * 佐商服务号微信处理类
 * Class DevWeChatProcess
 * @package App\Service\WeChat\OfficialAccount
 * @author zxqc2018
 */
class GzhWeChatProcess implements WeChatProcessInterface
{

    /**
     * 公众号对象
     * @var
     */
    protected $weChatApp;

    /**
     * 获取WeChat app
     * @return WeChatApp
     * @author zxqc2018
     */
    function getWeChatApp() : WeChatApp
    {
        if (!is_null($this->weChatApp)) {
            return $this->weChatApp;
        }
        $config = [
            'app_id' => ConfigParams::getParamWithController(ConfigParams::JQ_GZH_WX_ID),
            'secret' => ConfigParams::getParamWithController(ConfigParams::JQ_GZH_WX_SECRET),
        ];

        $this->weChatApp =  new GzhWeChatApp($config);
        return $this->weChatApp;
    }

    /**
     * 微信授权code获取openid
     * @param string|null $code 授权code
     * @param bool $onlyOpenid 是否只是取得opneid false 则根据access_token再取得用户信息
     * @return array
     * @author zxqc2018
     */
    function getOpenidByCode($code = null, $onlyOpenid = true)
    {
        $app = $this->getWeChatApp();
        $user = [];
        try {
            $res = $app->getWeChatDocument()->getOpenidByCode($code);
            if ($res->getCode() == 0) {
                $user = $res->getData();
                if (!empty($user['openid'])) {
                    //根据access_token获取用户id
                    if (!$onlyOpenid) {
                        $res = $app->getWeChatDocument()->getWeChatUserInfoByToken($user['access_token'], $user['openid']);
                        if ($res->getCode() == 0) {
                            $user = $res->getData();
                        }
                    }
                }
            }
        }catch (\Exception $e) {
        }


        //兼容原来easyWeChat返回值
        if (!empty($user['openid'])) {
            $user['id'] = $user['openid'];
        }

        if (!empty($user['headimgurl'])) {
            $user['avatar'] = $user['headimgurl'];
        }
        return $user;
    }
}
