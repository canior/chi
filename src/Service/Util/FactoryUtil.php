<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/4
 * Time: 19:36
 */

namespace App\Service\Util;


use App\Service\Order\OfflineTableNo;
use App\Service\Pay\Contracts\GatewayInterface;
use App\Service\Pay\Contracts\NotifyInterface;
use App\Service\Pay\NotifyProcess;
use App\Service\Pay\Pay;
use App\Service\Sms\AliSms;

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

    /**
     * 获取阿里短信服务类
     * @return AliSms
     * @author zxqc2018
     */
    public static function aliSms()
    {
        return new AliSms();
    }

    /**
     * 支付宝异步通知服务类
     * @return NotifyInterface|GatewayInterface
     * @author zxqc2018
     */
    public static function aliPayNotify()
    {
        return Pay::getInstance()->driver(Pay::ALI_PAY_DRIVER)->notify();
    }

    /**
     * 微信异步通知服务类
     * @return NotifyInterface|GatewayInterface
     * @author zxqc2018
     */
    public static function wxPayNotify()
    {
        return Pay::getInstance()->driver(Pay::WX_PAY_DRIVER)->notify();
    }

    /**
     * 根据异步通知数据获取处理对象
     * @param string $notifyRaw 异步通知字符串
     * @return NotifyProcess
     * @author zxqc2018
     */
    public static function notifyProcess($notifyRaw)
    {
        $notifyProcess = new NotifyProcess($notifyRaw);
        return $notifyProcess;
    }
}