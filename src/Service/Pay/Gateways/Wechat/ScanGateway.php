<?php
namespace App\Service\Pay\Gateways\Wechat;

use App\Service\Pay\Gateways\Wechat;

/**
 * 微信扫码支付网关
 * Class ScanGateway
 * @package App\Service\Pay\Gateways\Wechat
 */
class ScanGateway extends Wechat
{

    /**
     * 当前操作类型
     * @return string
     */
    protected function getTradeType()
    {
        return 'NATIVE';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return mixed
     */
    public function apply(array $options = [])
    {
        return $this->preOrder($options)['code_url'];
    }
}
