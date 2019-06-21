<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 9:35
 */

namespace App\Service\Pay\Contracts;


use App\Service\ResultData;

interface NotifyInterface
{
    /**
     * @param ResultData $notifyVerifyResult
     * @return ResultData
     * @author zxqc2018
     */
    public function notify(ResultData $notifyVerifyResult): ResultData;

    /**
     * @param string $notifyRaw
     * @return ResultData
     * @author zxqc2018
     */
    public function notifyVerify(string $notifyRaw): ResultData;

    /**
     * @param ResultData $notifyVerifyData
     * @return string
     * @author zxqc2018
     */
    public function getOutTradeNo(ResultData $notifyVerifyData) : string ;
}