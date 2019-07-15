<?php
namespace App\Service\Pay\Gateways\Yinlian;

use App\Service\Pay\Gateways\Yinlian;

/**
 * 微信App支付网关
 * Class AppGateway
 * @package App\Service\Pay\Gateways\Yinlian
 */
class AppGateway extends Yinlian
{
    /**
     * 当前操作类型
     * @return string
     */
    protected function getTradeType()
    {
        return '';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return array
     */
    public function apply(array $options = [])
    {
        $payRequest['sign'] = $this->preOrder($options);
        return $payRequest;
    }
}
