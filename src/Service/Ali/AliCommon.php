<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-04-11
 * Time: 5:08 PM
 */

namespace App\Service\Ali;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;

class AliCommon
{

    const VIDEO_FORMAT_MP4 = 'mp4';
    const VIDEO_FORMAT_M3U8 = 'm3u8';
    private $client;

    /**
     * @throws ClientException
     */
    public function __construct() {
        $regionId = 'cn-shanghai';
        $appId = 'LTAI7aN9Euuf3TBj';
        $appSecret = '3EqmErC0GeV8SQGar9Zmy4TQ88Zgjy';
        $this->client = AlibabaCloud::accessKeyClient($appId, $appSecret)
            ->regionId($regionId)->asDefaultClient();
    }

    /**
     * @param $videoId
     * @param string $formatType
     * @return array
     * @throws ClientException
     * @throws ServerException
     */
    public function getPlayInfo($videoId, $formatType = self::VIDEO_FORMAT_MP4)
    {
        $request = AlibabaCloud::vod()->v20170321()->getPlayInfo();
        return $request
            ->withVideoId($videoId)
            ->withFormats($formatType)
            ->request()->toArray();
    }

    /**
     * 阿里发送短信单条
     * @param $phone
     * @param $tempData
     * @param $templateId
     * @param string $signName
     * @author zxqc2018
     * @return \App\Service\ResultData
     */
    public function sendMsg($phone, $tempData, $templateId, $signName = '别吵网')
    {
        $res = CommonUtil::resultData();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $phone,
                        'SignName' => $signName,
                        'TemplateCode' => $templateId,
                        'TemplateParam' => is_array($tempData) ? json_encode($tempData) : $tempData,
                    ],
                ])
                ->request();
            $responseArr = $result->toArray();
            $message = $responseArr['Message'] ?? '';
            if ($message !== 'OK') {
                $res->throwErrorException(ErrorCode::ERROR_SMS_SEND_RESPONSE, [], $message);
            }
            $res->setData($result->toArray());
        } catch (ClientException $e) {
            $res->throwErrorException(ErrorCode::ERROR_SMS_SEND_RESPONSE, [], $e->getErrorMessage());
        } catch (ServerException $e) {
            $res->throwErrorException(ErrorCode::ERROR_SMS_SEND_RESPONSE, [], $e->getErrorMessage());
        }
        return $res;
    }
}
