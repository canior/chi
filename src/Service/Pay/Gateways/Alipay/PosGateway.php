<?php
namespace App\Service\Pay\Gateways\Alipay;

use App\Service\Pay\Gateways\Alipay;

/**
 * 支付宝刷卡支付
 * Class PosGateway
 * @package App\Service\Pay\Gateways\Alipay
 */
class PosGateway extends Alipay
{

    /**
     * 当前接口方法
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.pay';
    }

    /**
     * 当前接口产品码
     * @return string
     */
    protected function getProductCode()
    {
        return 'FACE_TO_FACE_PAYMENT';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @param string $scene
     * @return array|bool
     */
    public function apply(array $options = [], $scene = 'bar_code')
    {
        $options['scene'] = $scene;
        return $this->getResult($options, $this->getMethod());
    }
}
