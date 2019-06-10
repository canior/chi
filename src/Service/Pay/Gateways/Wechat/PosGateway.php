<?php
namespace App\Service\Pay\Gateways\Wechat;

use App\Service\Pay\Gateways\Wechat;

/**
 * 微信POS刷卡支付网关
 * Class PosGateway
 * @package App\Service\Pay\Gateways\Wechat
 */
class PosGateway extends Wechat
{

    /**
     * 当前操作类型
     * @return string
     */
    protected function getTradeType()
    {
        return 'MICROPAY';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return array
     */
    public function apply(array $options = [])
    {
        unset($this->config['trade_type']);
        unset($this->config['notify_url']);
        $this->gateway = $this->gateway_micropay;
        return $this->preOrder($options);
    }

}
