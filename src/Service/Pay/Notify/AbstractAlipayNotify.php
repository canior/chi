<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 12:18
 */

namespace App\Service\Pay\Notify;


use App\Service\Pay\Contracts\NotifyInterface;
use App\Service\Pay\Gateways\Alipay;

abstract class AbstractAlipayNotify extends Alipay implements NotifyInterface
{

    /**
     * @return string
     */
    protected function getMethod()
    {
        // TODO: Implement getMethod() method.
    }

    /**
     * @return string
     */
    protected function getProductCode()
    {
        // TODO: Implement getProductCode() method.
    }
}