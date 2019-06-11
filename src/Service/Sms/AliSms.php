<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 10:58
 */

namespace App\Service\Sms;


use AlibabaCloud\Client\Exception\ClientException;
use App\Service\Ali\AliCommon;
use App\Service\ErrorCode;
use App\Service\ResultData;
use App\Service\Util\CommonUtil;

/**
 * 阿里短信发送服务类
 * Class AliSms
 * @package App\Service\Sms
 * @author zxqc2018
 */
class AliSms extends AbstractSms
{
    /**
     * @var AliCommon
     */
    private $client;

    public function __construct()
    {
        try {
            $this->client = new AliCommon();
        } catch (ClientException $e) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_SMS_CLIENT_INIT, [], $e->getMessage());
        }
    }

    /**
     * 发送文本通知
     * @param mixed $targetUser 目标用户
     * @param array $tempData 模板数据
     * @param int $templateId 消息摸板 0 默认不需要模板
     * @return mixed
     * @author zxqc2018
     */
    function send($targetUser, $tempData, $templateId): ResultData
    {
        $targetUser = CommonUtil::myExplode($targetUser, ',', false);
        return $this->client->sendMsg($targetUser, $tempData, $templateId);
    }

    /**
     * 批量发送短信通知
     * @param mixed $targetUsers 目标用户们
     * @param array $tempData 模板数据
     * @param int $templateId 消息摸板 0 默认不需要模板
     * @return mixed
     * @author zxqc2018
     */
    function sendBatch($targetUsers, $tempData, $templateId): ResultData
    {
        // TODO: Implement sendBatch() method.
    }
}