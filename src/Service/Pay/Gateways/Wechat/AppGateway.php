<?php
namespace App\Service\Pay\Gateways\Wechat;

use App\Service\Pay\Gateways\Wechat;

/**
 * 微信App支付网关
 * Class AppGateway
 * @package App\Service\Pay\Gateways\Wechat
 */
class AppGateway extends Wechat
{

    /**
     * 当前操作类型
     * @return string
     */
    protected function getTradeType()
    {
        return 'APP';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return array
     */
    public function apply(array $options = [])
    {
        $payRequest = [
            'appid'     => $this->userConfig->get('app_id'),
            'partnerid' => $this->userConfig->get('mch_id'),
            'prepayid'  => $this->preOrder($options)['prepay_id'],
            'timestamp' => time() . '',
            'noncestr'  => $this->createNonceStr(),
            'package'   => 'Sign=WXPay',
        ];

        $payRequest['sign'] = $this->getSign($payRequest);
        return $payRequest;
    }

}
