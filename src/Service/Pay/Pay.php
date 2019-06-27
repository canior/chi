<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/10
 * Time: 13:26
 */
namespace App\Service\Pay;

use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\Pay\Contracts\Config;
use App\Service\Pay\Contracts\GatewayInterface;
use App\Service\Pay\Contracts\NotifyInterface;
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
     * @var GatewayInterface $gateways
     */
    private $gateways;

    /**
     * Pay constructor.
     * @param array $wxAppConfig
     */
    public function __construct(array $wxAppConfig = [])
    {
        $defaultConfig = $this->getConfigSetting();
        if (!empty($wxAppConfig)) {
            $defaultConfig['wechat'] = array_merge($defaultConfig['wechat'], $wxAppConfig);
        }
        $this->config = new Config($defaultConfig);
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
                'app_id'     => ConfigParams::getParamWithController(ConfigParams::JQ_APP_WX_ID),
                // 微信支付商户号
                'mch_id'     => ConfigParams::getParamWithController(ConfigParams::JQ_APP_WX_MCH_ID),
                /*
                 // 子商户公众账号ID
                 'sub_appid'  => '子商户公众账号ID，需要的时候填写',
                 // 子商户号
                 'sub_mch_id' => '子商户号，需要的时候填写',
                */
                // 微信支付密钥
                'mch_key'    => ConfigParams::getParamWithController(ConfigParams::JQ_APP_WX_MCH_SECRET),
                // 微信证书 cert 文件
//                'ssl_cer'    => __DIR__ . '/cert/1300513101_cert.pem',
//                // 微信证书 key 文件
//                'ssl_key'    => __DIR__ . '/cert/1300513101_key.pem',
                // 缓存目录配置
                'cache_path' => '',
                // 支付成功通知地址
                'notify_url' => ConfigParams::getParamWithController(ConfigParams::JQ_APP_WXPAY_NOTIFY_URL),
                // 网页支付回跳地址
                'return_url' => '',
            ],
            // 支付宝支付参数
            'alipay' => [
                // 沙箱模式
                'debug'       => false,
                // 应用ID
                'app_id'      => ConfigParams::getParamWithController(ConfigParams::JQ_APP_ALIPAY_ID),
                // 支付宝公钥(1行填写)
                'public_key'  => ConfigParams::getParamWithController(ConfigParams::JQ_APP_ALIPAY_PUBLIC_KEY),
                // 支付宝私钥(1行填写)
                'private_key' => ConfigParams::getParamWithController(ConfigParams::JQ_APP_ALIPAY_MCH_PRIVATE_KEY),
                // 缓存目录配置
                'cache_path'  => '',
                // 支付成功通知地址
                'notify_url'  => ConfigParams::getParamWithController(ConfigParams::JQ_APP_ALIPAY_NOTIFY_URL),
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
     * 指定操作通知类
     * @return NotifyInterface|GatewayInterface
     */
    public function notify()
    {
        if (!isset($this->drivers)) {
            CommonUtil::resultData([], ErrorCode::ERROR_PAY_COMMON, 'Driver is not defined.')->throwErrorException();
        }
        return $this->createNotify();
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

    /**
     * 创建异步通知对象
     */
    protected function createNotify()
    {
        if (!file_exists(__DIR__ . '/Notify/' . ucfirst($this->drivers) . 'Notify.php')) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_PAY_COMMON, [], "Notify [{$this->drivers}] is not supported.");
        }

        $notify = __NAMESPACE__ . '\\Notify\\' . ucfirst($this->drivers) . 'Notify';

        /**
         * @var  NotifyInterface|GatewayInterface $notifyInstance
         */
        $notifyInstance =  new $notify();
        $notifyInstance->init($this->config->get($this->drivers));
        return $notifyInstance;
    }
}
