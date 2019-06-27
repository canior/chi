<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 9:04
 */

namespace App\Service\Pay\Notify;

use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\ResultData;
use App\Service\Util\CommonUtil;

class WechatNotify extends AbstractWechatNotify
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
        $res->forceSetMsg('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
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
        $data = $this->verify($notifyRaw);

        if (empty($data)) {
            $res->throwErrorException(ErrorCode::ERROR_NOTIFY_VERIFY_SIGN, []);
        }

        $resultCode = $data['result_code'] ?? '';
        $returnCode = $data['return_code'] ?? '';
        if ($resultCode != 'SUCCESS' || $returnCode != 'SUCCESS') {
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