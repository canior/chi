<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/10
 * Time: 13:26
 */
namespace App\Service\Pay;

use App\Service\ErrorCode;
use App\Service\Pay\Contracts\Config;
use App\Service\Pay\Contracts\GatewayInterface;
use App\Service\Util\CommonUtil;


/**
 * Class Pay
 * @package App\Service\Pay
 * @author zxqc2018
 */
class Pay
{
    const ALI_PAY_DRIVER = 'alipay';
    const WX_PAY_DRIVER = 'wechat';

    const APP_GATEWAY = 'app';
    const BILL_GATEWAY = 'bill';
    const POS_GATEWAY = 'pos';
    const SCAN_GATEWAY = 'scan';
    const TRANSFER_GATEWAY = 'transfer';
    const WAP_GATEWAY = 'wap';
    const WEB_GATEWAY = 'web';
    const BANK_GATEWAY = 'bank';
    const MINI_APP_GATEWAY = 'miniapp';
    const MP_GATEWAY = 'mp';
    /**
     * @var Pay
     */
    private static $instance;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $drivers;

    /**
     * @var string
     */
    private $gateways;

    /**
     * Pay constructor.
     */
    public function __construct()
    {
        $this->config = new Config($this->getConfigSetting());
    }

    /**
     * @return Pay
     * @author zxqc2018
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 获取支付相关配置
     * @return array
     * @author zxqc2018
     */
    protected function getConfigSetting()
    {
         return [
            // 微信支付参数
            'wechat' => [
                // 沙箱模式
                'debug'      => false,
                // 应用ID
                'app_id'     => 'wx748c006066232bc7',
                // 微信支付商户号
                'mch_id'     => '1537297651',
                /*
                 // 子商户公众账号ID
                 'sub_appid'  => '子商户公众账号ID，需要的时候填写',
                 // 子商户号
                 'sub_mch_id' => '子商户号，需要的时候填写',
                */
                // 微信支付密钥
                'mch_key'    => '09ssUqy9LsywDqImTgPTF96KYD7lud9X',
                // 微信证书 cert 文件
//                'ssl_cer'    => __DIR__ . '/cert/1300513101_cert.pem',
//                // 微信证书 key 文件
//                'ssl_key'    => __DIR__ . '/cert/1300513101_key.pem',
                // 缓存目录配置
                'cache_path' => '',
                // 支付成功通知地址
                'notify_url' => '',
                // 网页支付回跳地址
                'return_url' => '',
            ],
            // 支付宝支付参数
            'alipay' => [
                // 沙箱模式
                'debug'       => true,
                // 应用ID
                'app_id'      => '2016092900621247',
                // 支付宝公钥(1行填写)
                'public_key'  => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtU71NY53UDGY7JNvLYAhsNa+taTF6KthIHJmGgdio9bkqeJGhHk6ttkTKkLqFgwIfgAkHpdKiOv1uZw6gVGZ7TCu5LfHTqKrCd6Uz+N7hxhY+4IwicLgprcV1flXQLmbkJYzFMZqkXGkSgOsR2yXh4LyQZczgk9N456uuzGtRy7MoB4zQy34PLUkkxR6W1B2ftNbLRGXv6tc7p/cmDcrY6K1bSxnGmfRxFSb8lRfhe0V0UM6pKq2SGGSeovrKHN0OLp+Nn5wcULVnFgATXGCENshRlp96piPEBFwneXs19n+sX1jx60FTR7/rME3sW3AHug0fhZ9mSqW4x401WjdnwIDAQAB',
                // 支付宝私钥(1行填写)
                'private_key' => 'MIIEowIBAAKCAQEAt14rzUC3mIGI+L3eFCe0O5hsW8YK+4J1eTTVJ1cHsykYdMtlJ2foIV602f6KcHNOz9WRvBwpvNvoR6QEE8Ox9/Rc7NRUZRlw4SC2YOWgpYAIgMLypOZK3MiI+0OexeX++oT0UmPCq14qg8mJPN++QsljrLS8PWsLPvmr6znKYRjnR96oVsQbaLTIiFB5dfsXbtCyiWBVnqsfMtb119DpDXiPytf0bv7yh+eN0obul02Hc8MwS7Bo+MA9RKBm/41+rfme6ZBxt7oLtRSf6sTDO/9lPgy/IS+RXmpTP6NXUWfJyaM74aNF4Th3G//sylbtQ5o3UfVE2e8AqhdZBXxSZQIDAQABAoIBAFVgQWC1y0X0ugOm06nqup4s3/bzBNYJ7gxbLwu8F9exQLPQp+5rEMfIwsflHiwxY/ac59f//Ob0NPh2Q3e4XDqoykNysUDJUadghcoj4GJudIcPPc7NcvnNXQowd9KIvemC0gcyb8c+dukZgw6W1o1eG0ykcijemEPgF83UAIioN9DImOCZcjZb0xnm+DNdiFf4xARwasKyUjgVOag0zEnT7I6zjb6jn4Yw+JI+qTPIw6dO/N7vH2DihOsx7HM5rVUWF9TrZlz7kdftlKkRR0yrGnPjjBEWAruZ7diwpZHjeI/SvcYzFaFyRRwe0Wjw7hAyui3pwdr3lFnimOEs/5kCgYEA4PbkxHqnrUlrXV/oNd9QW0unIiLy3MCJyeqMAjin2YwDrMe3aZVe1pberk/6r1lRAABKnDIX+WAOxgPeTMUkH10IHmzhXgUEmau41yEtYb7k8oyMaIeoYmTwEtaQGU1j+50ZvpafCTi75EyVFGpHApDiIXoeMytvwuZkiocBBCcCgYEA0Ko0UWr3X/qfvDhp/c0VhSOsV1Lxf1oV8vyOY62JTUO9sDuwVhP0hhrgts9rcsi3jfpAuBeMZxKT/IhMLmuw5bYGpxqu4bFiP1E6heZzuNMkwgAPu8onSr9Quk5Xf27oA+gmpYnUosWVIi9gzGWc/NxKkH5bdXHPvJpQI3uHkJMCgYEAnP6wGA4hBmW+b6OGwlHeGlhpgwEwy63yet8cZlBzkvaLegDGlwCO9uLC9JMMN2L1jDKn3ul2oanPpZD9ikXqN5kNGW6SuLJ3y2Zz0G5u5U/7Jum+8xP5BvG6OOtUFNRVejrgxIkI476cIW7wexbQB5JkvUgj20Hs9O8kKyicJPECgYByy+DClh2IISK18zdkmOpo9+o9lHUmAhKnPoi7j+JErqryBLSLdqkFCk5sZIqReJl7M51dah8lKZRez0FSHI8SoWThlA14PxV1DiQrPFCX8xl63XvKbXnWZpBsHuOGSSa9139DfSONdTQJvZT8fj6y4iLngYyhfT6zXRYPn0tCnwKBgBjiDaTNWhX+KRAha6mB5HKDGFwwmgf4Sg4GXnBJl886cV8RCMaauPLTQEcqPZ4MmnPX1W1K3F2WjMO+ZQIjg6gJgiWev5FBN10J8L3SAh0gLxJjWpCRHT/UAEY3yLxn0ZDZ2gNGHFcj2PGv4LwebYjzfIs9CfJDCLIwXyIEd1eb',
                // 缓存目录配置
                'cache_path'  => '',
                // 支付成功通知地址
                'notify_url'  => 'https://laowantong.yunlishuju.com/pay/notify',
                // 网页支付回跳地址
                'return_url'  => '',
            ],
        ];
    }
    /**
     * 指定驱动器
     * @param string $driver
     * @return $this
     */
    public function driver($driver)
    {
        if (is_null($this->config->get($driver))) {
            CommonUtil::resultData([], ErrorCode::ERROR_PAY_COMMON, "Driver [$driver]'s Config is not defined.")->throwErrorException();
        }
        $this->drivers = $driver;
        return $this;
    }

    /**
     * 指定操作网关
     * @param string $gateway
     * @return GatewayInterface
     */
    public function gateway($gateway = 'web')
    {
        if (!isset($this->drivers)) {
            CommonUtil::resultData([], ErrorCode::ERROR_PAY_COMMON, 'Driver is not defined.')->throwErrorException();
        }
        return $this->gateways = $this->createGateway($gateway);
    }

    /**
     * 创建操作网关
     * @param string $gateway
     * @return GatewayInterface
     */
    protected function createGateway($gateway)
    {
        if (!file_exists(__DIR__ . '/Gateways/' . ucfirst($this->drivers) . '/' . ucfirst($gateway) . 'Gateway.php')) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_PAY_COMMON, [], "Gateway [$gateway] is not supported.");
        }

        $gateway = __NAMESPACE__ . '\\Gateways\\' . ucfirst($this->drivers) . '\\' . ucfirst($gateway) . 'Gateway';

        /**
         * @var GatewayInterface $gatewayInstance
         */
        $gatewayInstance =  new $gateway();
        $gatewayInstance->init($this->config->get($this->drivers));
        return $gatewayInstance;
    }

}
