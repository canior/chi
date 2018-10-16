<?php
namespace App\Service\Utils;

use App\Service\Traits\WxGzTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class WxGzUtil
{
    use WxGzTrait;

    private $container;
    private $appId;
    private $appSecret;
    private $token;
    private $encodingAesKey;
    private $logger;
    private $requestStack;
    private $request;
    private $debug;

    public function __construct(ContainerInterface $container, RequestStack $requestStack) {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->requestStack = $requestStack;
        $this->request = $this->requestStack->getCurrentRequest();

        $this->appId = $this->container->getParameter('wx_gz_appkey');
        $this->appSecret = $this->container->getParameter('wx_gz_secret');
        $this->token = $this->container->getParameter('wx_gz_token');
        $this->encodingAesKey = $this->container->getParameter('wx_gz_aes_key');
        $this->debug = $this->container->getParameter('wx_gz_debug');

        if ($this->appId == null
                or $this->appSecret == null
                or $this->token == null) {
            $this->logger->error('wx_gz_params_empty', [$this->appId, $this->appSecret, $this->token]);
            throw new \Exception("wx_gz_params_empty");
        }

        $this->logger->info('checkSignature:'.var_export($this->checkSignature(), true));
        if ($this->checkSignature()!=false) {
            echo $this->checkSignature();exit();
        }
    }

    public function getAccessToken() {
        $cache = $this->getCache();
        if ( $this->getCacheItem($cache, 'access_token') ) {
            return $this->getCacheItem($cache, 'access_token');
        } else {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
            $url = sprintf($url, $this->appId, $this->appSecret);
            $data = json_decode( file_get_contents($url), true);
            if (isset($data['access_token'])) {
                $this->saveCacheItem($cache, 'access_token', $data['access_token']);
                return $data['access_token'];
            } else {
                $this->logger->error('wx_gz_token_get_failed', (array)$data);
                throw new \Exception("wx_gz_token_get_failed");
            }
        }
    }

    private function checkSignature() {
        $token = $this->token;
        $signature = $this->request->query->get('signature');
        $timestamp = $this->request->query->get('timestamp');
        $nonce = $this->request->query->get('nonce');
        $echostr = $this->request->query->get('echostr');

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $signature == $tmpStr ){
            return $echostr;
        }else{
            return false;
        }
    }

    public function parseXml($content) {
        $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }

    public function getUserInfo($accessToken, $openId) {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
        $url = sprintf($url, $accessToken, $openId);
        $data = json_decode( file_get_contents($url), true);
        $this->logger->info('user_info', [$url, $data]);
        return $data;
    }

    public function isLogin() {
        return false;
    }

    public function getOauthUrl($redirectUrl) {
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
        return sprintf($url, $this->appId, $redirectUrl);
    }

    public function getOauthAccessToken($code) {
        $cache = $this->getCache('wx_gz_oauth');
        if ( $this->getCacheItem($cache, 'access_token_'.$code) ) {
            return $this->getCacheItem($cache, 'access_token_'.$code);
        } else {
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
            $url = sprintf($url, $this->appId, $this->appSecret, $code);
            $data = json_decode( file_get_contents($url), true);
            if (isset($data['access_token'])) {
                $tmp = [
                    'access_token' => $data['access_token'],
                    'openid' => $data['openid'],
                ];
                $this->saveCacheItem($cache, 'access_token_'.$code, $tmp);
                return $tmp;
            } else {
                $this->logger->error('wx_gz_oauth_token_get_failed', (array)$data);
                throw new \Exception("wx_gz_oauth_token_get_failed");
            }
        }
    }

    public function getOauthUserInfo ($accessToken, $openId) {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
        $url = sprintf($url, $accessToken, $openId);
        $data = json_decode( file_get_contents($url), true);
        return $data;
    }

    public function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    public function createJsConfig($url) {
        $timestamp = time();
        $nonceStr = $this->getNonceStr();
        $jsTicket = $this->getJsTicket();

        $sign = [
            'jsapi_ticket='.$jsTicket,
            'noncestr='.$nonceStr,
            'timestamp='.$timestamp,
            'url='.$url,
        ];
        $signature = $tmpStr = sha1( implode('&', $sign) );

        $jsStr = "wx.config({
            debug: {$this->debug},
            appId: '{$this->appId}',
            timestamp: '{$timestamp}',
            nonceStr: '{$nonceStr}',
            signature: '{$signature}',
            jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'getLocation']
        });";
        return $jsStr;
    }

    public function getJsTicket() {
        $cache = $this->getCache('wx_gz_js_ticket');
        if ( $this->getCacheItem($cache, 'js_ticket') ) {
            return $this->getCacheItem($cache, 'js_ticket');
        } else {
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi';
            $url = sprintf($url, $this->getAccessToken());
            $data = json_decode( file_get_contents($url), true);
            if (isset($data['ticket'])) {
                $this->saveCacheItem($cache, 'js_ticket', $data['ticket']);
                return $data['ticket'];
            } else {
                $this->logger->error('wx_gz_js_ticket_get_failed', (array)$data);
                throw new \Exception("wx_gz_js_ticket_get_failed");
            }
        }
    }

    public function sendNewsBody($toUserName, $fromUserName, $data, $count = 1) {
        $time = time();
        $result = "<xml>
                    <ToUserName><![CDATA[{$toUserName}]]></ToUserName>
                    <FromUserName><![CDATA[{$fromUserName}]]></FromUserName>
                    <CreateTime>{$time}</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>{$count}</ArticleCount>
                    <Articles>";
        $tmp = "";
        foreach ($data as $k => $art) {
            $tmp .= "<item>
                    <Title><![CDATA[{$art['title']}]]></Title>
                    <Description><![CDATA[{$art['descp']}]]></Description>
                    <PicUrl><![CDATA[{$art['picUrl']}]]></PicUrl>
                    <Url><![CDATA[{$art['url']}]]></Url>
                    </item>";
        }
        $result .= $tmp . "</Articles></xml>";
        return $result;
    }

    public function sendTextBody($toUserName, $fromUserName, $content) {
        $time = time();
        $result = "<xml>
                    <ToUserName><![CDATA[{$toUserName}]]></ToUserName>
                    <FromUserName><![CDATA[{$fromUserName}]]></FromUserName>
                    <CreateTime>{$time}</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[{$content}]]></Content>
                    </xml>";
        return $result;
    }
}