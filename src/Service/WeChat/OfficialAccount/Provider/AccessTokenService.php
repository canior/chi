<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 22:38
 */

namespace App\Service\WeChat\OfficialAccount\Provider;


use App\Entity\ProjectTokenMeta;
use App\Service\Config\ConfigParams;
use App\Service\Document\WeChatDocument;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;

/**
 * accessToken处理类
 * Class AccessTokenService
 * @package App\WeChat\OfficialAccount\Provider
 * @author zxqc2018
 */
class AccessTokenService
{
    /**
     * @var WeChatDocument
     */
    private $weChatDocument;
    /**
     * @var string
     */
    private $appId;
    /**
     * @var string
     */
    private $secret;

    const ACCESS_TOKEN = 'token';
    const JS_TICKET = 'jsTicket';

    private $expireCacheTime = 7200;
    public function __construct(WeChatDocument $weChatDocument)
    {
        $this->appId = $weChatDocument->getAppId();
        $this->secret = $weChatDocument->getSecret();
        $this->weChatDocument = $weChatDocument;
    }

    /**
     * 获取app token 数据库配置
     * @param $type
     * @return string
     * @author zxqc2018
     */
    public function getAppIdProjectTokenMap($type)
    {
        $config =  [
            ConfigParams::getParamWithController(ConfigParams::JQ_GZH_WX_ID) => [
                'token' => ProjectTokenMeta::JQ_GZH_WX_TOKEN,
                'jsTicket' => ProjectTokenMeta::JQ_GZH_WX_JS_TOKEN,
            ]
        ];

        return $config[$this->appId][$type] ?? '';
    }

    public function getTokenKey()
    {
        return $this->getAppIdProjectTokenMap(self::ACCESS_TOKEN);
    }

    public function getJsTokenKey()
    {
        return $this->getAppIdProjectTokenMap(self::JS_TICKET);
    }

    /**
     * @param $metaKey
     * @return ProjectTokenMeta|null
     * @author zxqc2018
     */
    public static function findProjectToken($metaKey)
    {
        return FactoryUtil::projectTokenMetaRepository()->findOneBy(['metaKey' => $metaKey]);
    }

    /**
     * 保存token
     * @param $token
     * @author zxqc2018
     */
    public function setTokenCache($token)
    {
        $projectToken = self::findProjectToken($this->getTokenKey());
        if (empty($projectToken)) {
            $projectToken = new ProjectTokenMeta();
            $projectToken->setMetaKey($this->getTokenKey());
        }
        $projectToken->setToken($token);
        $projectToken->setExpiresAt(time() + $this->expireCacheTime);
        CommonUtil::entityPersist($projectToken);
    }

    public function getTokenCache()
    {
        $projectToken = self::findProjectToken($this->getTokenKey());
        return CommonUtil::getInsideValue($projectToken, 'getToken', '');
    }

    public function setJsTicketCache($token)
    {
        $projectToken = self::findProjectToken($this->getJsTokenKey());
        if (empty($projectToken)) {
            $projectToken = new ProjectTokenMeta();
            $projectToken->setMetaKey($this->getJsTokenKey());
        }
        $projectToken->setToken($token);
        $projectToken->setExpiresAt(time() + $this->expireCacheTime);
        CommonUtil::entityPersist($projectToken);
    }

    /**
     * @return string
     * @author zxqc2018
     */
    public function getJsTicketCache()
    {
        $projectToken = self::findProjectToken($this->getJsTokenKey());
        return CommonUtil::getInsideValue($projectToken, 'getToken', '');
    }

    /**
     * 获取token
     * @param bool $refresh 是否刷新
     * @return string
     * @author zxqc2018
     */
    public function getToken($refresh = false)
    {
        //不刷新直接取缓存
        if (!$refresh) {

            $accessToken = $this->getTokenCache();
            if (!empty($accessToken)) {
                return $accessToken;
            }

            if (empty($accessToken)) {
                $accessToken = $this->weChatDocument->getAccessToken();
            }

            if (empty($accessToken)) {
                CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_WX_TOKEN_GET, []);
            }
        }

        $accessToken =  $this->weChatDocument->getAccessToken();
        if (empty($accessToken)) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_WX_TOKEN_GET, []);
        }

        $this->setTokenCache($accessToken);
        return $accessToken;
    }


    protected function getTicketSignature($ticket, $nonce, $timestamp, $url): string
    {
        return sha1(sprintf('jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s', $ticket, $nonce, $timestamp, $url));
    }

    /**
     * 获取jssdk配置
     * @return string
     * @author zxqc2018
     */
    public function getJsTicket()
    {
        $token = $this->getToken();

        $jsTicket = $this->getJsTicketCache();
        if (empty($jsTicket)) {
            $jsTicket = $this->weChatDocument->getJsTicket($token);
        }

        if (empty($jsTicket)) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_WX_JS_TOKEN_GET, []);
        }
        $this->setJsTicketCache($jsTicket);

        return $jsTicket;
    }
}
