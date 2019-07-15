<?php

namespace App\Service\Pay\Gateways;

use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use App\Service\Pay\Contracts\Config;
use App\Service\Pay\Contracts\GatewayInterface;

use App\Service\Config\ConfigParams;
use App\Service\Pay\Contracts\HttpService;
use App\Service\Pay\Gateways\Yinlian\Utils\Utils;
use App\Service\Pay\Gateways\Yinlian\Utils\RequestHandler;
use App\Service\Pay\Gateways\Yinlian\Utils\ClientResponseHandler;
use App\Service\Pay\Gateways\Yinlian\Utils\PayHttpClient;

/**
 * 银联抽象类
 * Class Yinlian
 * @package App\Service\Pay\Gateways\Yinlian
 */
abstract class Yinlian extends GatewayInterface
{

    /**
     * 支付全局参数
     * @var array
     */
    protected $config;
    private $resHandler = null;
    private $reqHandler = null;
    private $pay = null;

    /**
     * Yinlian constructor.
     * @param array $config
     */
    public function init(array $config)
    {
        if (!isset($config['mchId']) || is_null($config['mchId']) ) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_ALI_PAY_CONFIG, ['mchId']);
        }

        $this->config = $config;

        //初始化 
        $this->Request();
    }


    public function Request(){
        $this->resHandler = new ClientResponseHandler();
        $this->reqHandler = new RequestHandler();
        $this->pay = new PayHttpClient();

        $this->reqHandler->setGateUrl($this->config['url']);
       
        $sign_type = $this->config['sign_type'];
        if ($sign_type == 'MD5') {
            $this->reqHandler->setKey($this->config['key']);
            $this->resHandler->setKey($this->config['key']);
            $this->reqHandler->setSignType($sign_type);
        } else if ($sign_type == 'RSA_1_1' || $sign_type == 'RSA_1_256') {
            $this->reqHandler->setRSAKey($this->config['private_rsa_key']);
            $this->resHandler->setRSAKey($this->config['public_rsa_key']);
            $this->reqHandler->setSignType($sign_type);
        }
    }

    /**
     * XML内容验证
     * @param string $data
     * @param null $sign
     * @param bool $sync
     * @return array|bool
     */
    public function verify($data, $sign = null, $sync = false)
    {
        $resdata = false;
        $this->resHandler->setContent($data);
        $this->resHandler->setKey($this->config['key']);
        if($this->resHandler->isTenpaySign()){
            $resdata = Utils::fromXml($data);
        }
        return $resdata;
    }


    /**
     * 应用参数
     * @param array $options
     * @return mixed|void
     */
    public function apply(array $options)
    {

    }

    /**
     * 银联订单退款操作
     * @param array|string $options 退款参数或退款商户订单号
     * @param null $refund_amount 退款金额
     * @return array|bool
     */
    public function refund($options, $refund_amount = null)
    {

    }

    /**
     * 关闭银联进行中的订单
     * @param array|string $options
     * @return array|bool
     */
    public function close($options)
    {

    }

    /**
     * 查询银联订单状态
     * @param string $out_trade_no
     * @return array|bool
     */
    public function find($out_trade_no = '')
    {

    }


    /**
     * @param array $options
     * @return array
     */
    protected function preOrder($options = [])
    {
        $this->config = array_merge($this->config, $options);

        $logPath = ConfigParams::getParamWithController('kernel.project_dir'). '/var/log/';

        if (is_dir($logPath)) {
            file_put_contents($logPath .'test.pay.log', json_encode($this->config) . "\n", FILE_APPEND);
        }
        return $this->getResult($this->config['url'],$options);
    }

    /**
     * 获取验证访问数据
     * @param array $options
     * @param string $method
     * @return array|bool
     */
    protected function getResult($gateway,$options)
    {
        $paydata = [
            'body' => $options['body'],
            'mch_create_ip' => $options['spbill_create_ip'],
            'out_trade_no' => $options['out_trade_no'],
            'total_fee' => $options['total_amount'],
            'sub_openid' => $options['sub_openid'],
        ];
        $this->reqHandler->setReqParams($paydata,array('method'));
        $this->reqHandler->setParameter('service','pay.weixin.jspay');//接口类型：pay.weixin.jspay
        $this->reqHandler->setParameter('mch_id',$this->config['mchId']);//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('version',$this->config['version']);
        $this->reqHandler->setParameter('sign_type',$this->config['sign_type']);
        $this->reqHandler->setParameter('notify_url',$this->config['notify_url']);//通知地址
        $this->reqHandler->setParameter('sub_appid',$this->config['sub_appid']);//对应公众号appid，必填
        $this->reqHandler->setParameter('is_raw','1');
        $this->reqHandler->setParameter('nonce_str',mt_rand());//随机字符串，必填项，不长于 32 位

        //创建签名
        $this->reqHandler->createSign();

        // 参数转为XML
        $data = Utils::toXml($this->reqHandler->getAllParameters());

        // 设置参数 发起请求
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
        if($this->pay->call()){
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            $res = $this->resHandler->getAllParameters();
            if($this->resHandler->isTenpaySign()){
                if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                    //当返回状态与业务结果都为0时继续判断
                    return array(
                        'status'=>500,
                        'msg'=>$this->resHandler->getParameter('message'),
                        'pay_info'=>json_decode($this->resHandler->getParameter('pay_info'),true)
                    );
                }else{
                    return array('status'=>500,'msg'=>$this->resHandler->getParameter('message').$this->resHandler->getParameter('err_code').$this->resHandler->getParameter('err_msg'));
                }
            }
            return array('status'=>500,'msg'=>$this->resHandler->getParameter('message'));
        }else{
            return array('status'=>500,'msg'=>'Response Code:'.$this->pay->getResponseCode().' Error Info:'.$this->pay->getErrInfo());
        }
    }
}
