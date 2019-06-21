<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 9:01
 */

namespace App\Service\Pay\Notify;


use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\ResultData;
use App\Service\Util\CommonUtil;

class AlipayNotify extends AbstractAlipayNotify
{
    use NotifyProcessTrait;
    /**
     * @param ResultData $notifyVerifyResult
     * @return ResultData
     * @author zxqc2018
     */
    public function notify(ResultData $notifyVerifyResult): ResultData
    {
        $outTradeNo = $this->getOutTradeNo($notifyVerifyResult);
        $res = $this->processOrder($outTradeNo);
        $res->forceSetMsg('SUCCESS');
        return $res;
    }

    /**
     * @param string $notifyRaw
     * @return ResultData
     * @author zxqc2018
     */
    public function notifyVerify(string $notifyRaw): ResultData
    {
        $res = CommonUtil::resultData();
        $tmpData = [];
        parse_str($notifyRaw, $tmpData);
        $data = $this->verify($tmpData, $tmpData['sign']);

        if (empty($data)) {
            $res->throwErrorException(ErrorCode::ERROR_NOTIFY_VERIFY_SIGN, []);
        }
        $res->setData($data);
        return $res;
    }

    /**
     * @param ResultData $notifyVerifyData
     * @return string
     * @author zxqc2018
     */
    public function getOutTradeNo(ResultData $notifyVerifyData): string
    {
        return $notifyVerifyData['out_trade_no'] ?? '';
    }
}