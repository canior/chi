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
    //您的短信验证码是：${code}，验证码20分钟内有效，请勿向他人泄露。
    const VERIFICATION_CODE_20 = 'SMS_168345248';

    //您的用户${name}完成了线下思维课的${money}元支付，并锁定了${num}天的推荐人关系，详细信息请打开app了解。
    const PARTNER_THINKING_WITHOUT_TABLE_NO = 'SMS_168340324';

    //您的用户${name}完成了线下思维课的${money}元支付，并锁定了${num}天的推荐人关系，坐席号为${tableNo}号桌，详细信息请打开app了解。
    const PARTNER_THINKING_WITH_TABLE_NO = 'SMS_168345303';

    //您的用户${name}完成了线下系统课的${money}元支付，坐席号为${tableNo}号桌，详细信息请打开app了解。
    const PARTNER_SYSTEM_WITH_TABLE_NO = 'SMS_168345353';

    //您的线下系统课报名成功，坐席号为${tableNo}号桌，详细信息请打开佐商学社了解。
    const PARTNER_SYSTEM_REGISTER_SELF = 'SMS_168340522';

    //很抱歉，由于您未进行系统学员身份的升级，您的系统课报名失败，可联系客服进行退款，详细信息请打开app了解。
    const VISITOR_SYSTEM_REGISTER_FAIL = 'SMS_168340527';

    //您的线下系统课报名成功，并且身份升级完成，您的坐席号为${tableNo}号桌，详细信息请打开app了解。
    const VISITOR_SYSTEM_REGISTER_SUCCESS = 'SMS_168345494';

    //您的线下系统课报名成功，坐席号为${tableNo}号桌，详细信息请打开佐商学社了解。
    const VISITOR_SYSTEM_REGISTER_REAL_SUCCESS = 'SMS_168340534';

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