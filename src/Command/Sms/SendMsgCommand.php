<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/11
 * Time: 20:13
 */

namespace App\Command\Sms;


use App\Command\SerializableCommandInterface;
use App\Command\Traits\SerializerTrait;

class SendMsgCommand implements SerializableCommandInterface
{
    use SerializerTrait;
    /**
     *  发送手机号
     * @var string
     */
    private $phone;

    /**
     * 发送模板内容
     * @var array $msgData
     */
    private $msgData;

    /**
     * 消息模板
     * @var string
     */
    private $msgTemplateId;

    /**
     * SendMsgCommand constructor.
     * @param string $phone
     * @param array $msgData
     * @param string $msgTemplateId
     */
    public function __construct(string $phone, array $msgData, string $msgTemplateId)
    {
        $this->phone = $phone;
        $this->msgData = $msgData;
        $this->msgTemplateId = $msgTemplateId;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return array
     */
    public function getMsgData(): array
    {
        return $this->msgData;
    }

    /**
     * @return string
     */
    public function getMsgTemplateId(): string
    {
        return $this->msgTemplateId;
    }
}