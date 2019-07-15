<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/21
 * Time: 9:45
 */

namespace App\Service\Pay;


use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\Pay\Contracts\NotifyInterface;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;

class NotifyProcess
{
    /**
     * @var string
     */
    private $notifyRaw;

    /**
     * @var NotifyInterface
     */
    private $processHandle;

    /**
     * NotifyProcess constructor.
     * @param string $notifyRaw
     */
    public function __construct(string $notifyRaw = '')
    {
        $this->notifyRaw = $notifyRaw;
        $this->initProcessHandle();
    }

    /**
     * 获取通知处理对象
     * @author zxqc2018
     */
    protected function initProcessHandle()
    {
        if (strpos($this->notifyRaw, ConfigParams::getParamWithController(ConfigParams::JQ_APP_WX_ID)) !== false) {
            $this->processHandle = FactoryUtil::wxPayNotify();
        } else if (strpos($this->notifyRaw, ConfigParams::getParamWithController(ConfigParams::JQ_APP_ALIPAY_ID)) !== false) {
            $this->processHandle = FactoryUtil::aliPayNotify();
        } else if (strpos($this->notifyRaw, ConfigParams::getParamWithController(ConfigParams::JQ_GZH_WX_ID)) !== false) {
            $this->processHandle = FactoryUtil::wxPayGzhNotify();
        }  else if (strpos($this->notifyRaw, ConfigParams::getParamWithController(ConfigParams::JQ_APP_YINLIAN_MCHID)) !== false) {
            $this->processHandle = FactoryUtil::yinlianPayNotify();
        }  else {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_NOTIFY_RAW_NOT_ALLOW, []);
        }
    }

    /**
     * 处理异步回调
     * @return \App\Service\ResultData
     * @author zxqc2018
     */
    public function process()
    {
        $verifyResult = $this->processHandle->notifyVerify($this->notifyRaw);
        return $this->processHandle->notify($verifyResult);
    }
}