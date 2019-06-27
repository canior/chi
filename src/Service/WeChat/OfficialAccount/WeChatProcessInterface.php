<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 23:00
 */

namespace App\Service\WeChat\OfficialAccount;


/**
 * 微信处理类接口
 * Interface WeChatProcessInterface
 * @package App\Service\WeChat\OfficialAccount
 */
interface WeChatProcessInterface
{

    /**
     * 获取WeChat app
     * @return WeChatApp
     * @author @author zxqc2018
     */
    function getWeChatApp() : WeChatApp;

    /**
     * 微信授权code获取openid
     * @param bool $onlyOpenid 是否只是取得opneid false 则根据access_token再取得用户信息
     * @param string|null $code 授权code
     * @return array
     * @author @author zxqc2018
     */
    function getOpenidByCode($onlyOpenid = true, $code = null);
}
