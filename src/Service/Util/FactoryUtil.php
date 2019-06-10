<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/4
 * Time: 19:36
 */

namespace App\Service\Util;


use App\Service\Pay\Pay;

class FactoryUtil
{

    /**
     * 支付宝支付网关获取
     * @param $gateway
     * @return \App\Service\Pay\Contracts\GatewayInterface
     * @author zxqc2018
     */
    public static function aliPayDriver($gateway)
    {
        return Pay::getInstance()->driver(Pay::ALI_PAY_DRIVER)->gateway($gateway);
    }

    /**
     * 微信支付网关获取
     * @param $gateway
     * @return \App\Service\Pay\Contracts\GatewayInterface
     * @author zxqc2018
     */
    public static function wxPayDriver($gateway)
    {
        return Pay::getInstance()->driver(Pay::WX_PAY_DRIVER)->gateway($gateway);
    }
}