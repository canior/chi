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
use AlibabaCloud\Client\Result\Result;

class AliCommon
{
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
     * @return array
     * @throws ClientException
     * @throws ServerException
     */
    public function getPlayInfo($videoId)
    {
        $request = AlibabaCloud::vod()->v20170321()->getPlayInfo();
        return $request
            ->withVideoId($videoId)
            ->withFormats('mp4')
            ->request()->toArray();
    }
}
