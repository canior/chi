<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 10:58
 */

namespace App\Service\Sms;


use App\Service\ResultData;

/**
 * 阿里短信发送服务类
 * Class AliSms
 * @package App\Service\Sms
 * @author zxqc2018
 */
class AliSms extends AbstractSms
{
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
        // TODO: Implement send() method.
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