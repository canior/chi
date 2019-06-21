<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 9:13
 */

namespace App\Service\Pay\Notify;



use App\Service\Pay\Contracts\NotifyInterface;
use App\Service\Pay\Gateways\Wechat;

abstract class AbstractWechatNotify extends Wechat implements NotifyInterface
{

    /**
     * 发起支付
     * @param array $options
     * @return mixed
     */
    public function apply(array $options)
    {
        // TODO: Implement apply() method.
    }

    /**
     * @return mixed
     */
    protected function getTradeType()
    {
        // TODO: Implement getTradeType() method.
    }
}