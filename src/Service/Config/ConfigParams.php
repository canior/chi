<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/20
 * Time: 13:05
 */

namespace App\Service\Config;

/**
 * 配置参数获取 config/parameters.yaml
 * Class ConfigParams
 * @package App\Service\Config
 * @author zxqc2018
 */
class ConfigParams
{
    //微信 金秋智库 app_id
    const JQ_APP_WX_ID = 'jq_app_wx_id';
    //微信 金秋智库 密钥
    const JQ_APP_WX_SECRET = 'jq_app_wx_secret';
    //微信佐商商户ID
    const JQ_APP_WX_MCH_ID = 'jq_app_wx_mch_id';
    //微信佐商商户支付密钥
    const JQ_APP_WX_MCH_SECRET = 'jq_app_wx_mch_secret';
    //微信支付成功通知url
    const JQ_APP_WXPAY_NOTIFY_URL = 'jq_app_wxpay_notify_url';

    //支付宝 金秋智库 app_id
    const JQ_APP_ALIPAY_ID = 'jq_app_alipay_id';
    //支付宝 金秋智库 商户私钥
    const JQ_APP_ALIPAY_MCH_PRIVATE_KEY = 'jq_app_alipay_mch_private_key';
    //支付宝 金秋智库 支付宝公钥
    const JQ_APP_ALIPAY_PUBLIC_KEY = 'jq_app_alipay_public_key';
    //支付宝 支付成功通知url
    const JQ_APP_ALIPAY_NOTIFY_URL = 'jq_app_alipay_notify_url';
    /**
     * 获取控制器为入口的容易的参数信息
     * @param $key
     * @param null $defaultValue
     * @return mixed
     * @author zxqc2018
     */
    public static function getParamWithController($key, $defaultValue = null)
    {
        $res = $defaultValue;
        $container = DependencyInjectionSingletonConfig::getInstance()->getContainer();

        if (empty($container)) {
            return $res;
        }

        if (!$container->hasParameter($key)) {
            return $res;
        }

        return $container->getParameter($key);
    }
}