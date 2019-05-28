<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 9:07
 */

namespace App\Service\Sms;


use App\Service\ResultData;

/**
 * 短信通知接口
 * Interface SmsInterface
 * @package App\Service\Sms
 */
interface SmsInterface
{
    /**
     * 发送文本通知
     * @param mixed $targetUser 目标用户
     * @param array $tempData 模板数据
     * @param int $templateId 消息摸板 0 默认不需要模板
     * @return mixed
     * @author zxqc2018
     */
    function send($targetUser, $tempData, $templateId) : ResultData;

    /**
     * 批量发送短信通知
     * @param mixed $targetUsers 目标用户们
     * @param array $tempData 模板数据
     * @param int $templateId 消息摸板 0 默认不需要模板
     * @return mixed
     * @author zxqc2018
     */
    function sendBatch($targetUsers, $tempData, $templateId) : ResultData;
}