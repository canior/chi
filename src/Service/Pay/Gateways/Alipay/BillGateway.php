<?php
namespace App\Service\Pay\Gateways\Alipay;

use App\Service\Pay\Gateways\Alipay;

/**
 * 支付宝电子面单下载
 * Class BillGateway
 * @package App\Service\Pay\Gateways\Alipay
 */
class BillGateway extends Alipay
{

    /**
     * 当前接口方法
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.data.dataservice.bill.downloadurl.query';
    }


    /**
     * 应用并返回参数
     * @return array|bool
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