<?php
namespace App\Service\Pay\Gateways\Alipay;

use App\Service\Pay\Gateways\Alipay;

/**
 * 支付宝扫码支付
 * Class ScanGateway
 * @package App\Service\Pay\Gateways\Alipay
 */
class ScanGateway extends Alipay
{

    /**
     * 当前接口方法
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.precreate';
    }

    /**
     * 当前接口产品码
     * @return string
     */
    protected function getProductCode()
    {
        return '';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return array|bool
     */
    public function apply(array $options = [])
    {
        return $this->getResult($options, $this->getMethod());
    }
}
