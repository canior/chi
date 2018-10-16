<?php
/**
 * User: YuYechao
 * Date: 2017-09-08
 */

namespace App\Service;

use App\Service\Traits\WxTrait;
use GuzzleHttp\Client;

/**
 * WxCommon
 * 微信小程序常用接口库
 */
class WxCommon
{
    use WxTrait;
    private $appId;
    private $appSecret;
    private $appKey;
    private $mchId;
    private $wxApiUrl = 'https://api.weixin.qq.com';
    private $wxNotifyUrl = 'https://api.yunlishuju.com/wxapi/common/notify';

    public function __construct($config = [])
    {
        $this->appId = $config['appid'];
        $this->appSecret = $config['appsecret'];
        if (isset($config['wxApiUrl'])) {
            $this->wxApiUrl = $config['wxApiUrl'];
        }
        if (isset($config['wxNotifyUrl'])) {
            $this->wxNotifyUrl = $config['wxNotifyUrl'];
        }
    }

    /**
     * 通过code获取session
     * https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html#wxloginobject
     * @param
     */
    public function getSessionByCode($code = '')
    {
        if (!$code) {
            return [
                'status' => false,
                'msg' => 'code_empty',
            ];
        }

        $client = new Client(['base_uri' => $this->wxApiUrl]);
        $info = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $paramStr = $this->formatBizQueryParaMap($info);
        $url = "sns/jscode2session?".$paramStr;
        $response = $client->get($url);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        if($code === 200) {
            $result = json_decode($body, true);
            if (isset($result['errcode'])) {
                return [
                    'status' => false,
                    'msg' => $result['errmsg'],
                    'code' => $result['errcode'],
                ];
            } else {
                return [
                    'status' => true,
                    'data' => $result,
                ];
            }
        } else {
            return [
                'status' => false,
                'msg' => $code
            ];
        }
    }

    /**
     * 获取access_token
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140183
     * @return string $accessToken | false
     */
    public function getAccessToken()
    {
        $client = new Client(['base_uri' => $this->wxApiUrl]);
        $url = "cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
        $response = $client->get($url);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        if($code === 200) {
            $result = json_decode($body, true);
            return $result['access_token'];
        } else {
            return false;
        }
    }

    /**
     * 获取二维码
     * @param array $params 二维码包含的数据参数
     */
    public function getQrcode($accessToken, $params)
    {
        if (!$accessToken) {
            return false;
        }

        $client = new Client(['base_uri' => $this->wxApiUrl]);
        $postInfo = [
            'scene' => urlencode( isset($params['scene']) ? $params['scene'] : '' ),
            'page' => isset($params['page']) ? $params['page'] : 'pages/index/index',
            'width' => isset($params['width']) ? $params['width'] : 430,
            'auto_color' => isset($params['auto_color']) ? $params['auto_color'] : true,
            'line_color' => isset($params['line_color']) ? $params['line_color'] : ['r'=>0, 'g'=>0, 'b'=>0],
        ];
        $url = "wxa/getwxacodeunlimit?access_token={$accessToken}";
        $postStr = json_encode($postInfo);
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody()->getContents();

        if($code === 200) {
            return $body;
        } else {
            return false;
        }
    }

    /**
     * 解密用户敏感数据
     * @param   $sessionKey
     * @param array $userInfo ($code, encryptedData, iv)
     */
    public function getDecryptData($sessionKey, $userInfo)
    {
        $result = $this->decryptData($sessionKey, $userInfo['encryptedData'], $userInfo['iv']);
        return $result;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $sessionKey
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    private function decryptData($sessionKey, $encryptedData, $iv)
    {
        if (strlen($sessionKey) != 24) {
            return ['err'=>'sessionKey_len_error'];
        }
        $aesKey = base64_decode($sessionKey);

        if (strlen($iv) != 24) {
            return ['err'=>'iv_len_error'];
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $data = json_decode($result, true);
        if( $data  == NULL )
        {
            return ['err'=>'illegal_buffer'];
        }
        if( $data['watermark']['appid'] != $this->appId )
        {
            return ['err'=>'illegal_buffer_appid'];
        }
        return $data;
    }
}