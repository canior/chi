<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/15
 * Time: 22:56
 */

namespace App\Service\Document;


use App\Service\ErrorCode;
use App\Service\ResultData;
use App\Service\Util\CommonUtil;

/**
 * 微信请求
 * Class WeChatDocument
 * @package App\Service\Document
 * @author zxqc2018
 */
class WeChatDocument extends AbstractDocument
{
    protected $config = [
        'host' => 'https://api.weixin.qq.com',
        'appid' => '',
        'secret' => '',
    ];

    public function getAppId()
    {
        return $this->config['appid'] ?? '';
    }


    public function getSecret()
    {
        return $this->config['secret'] ?? '';
    }

    /**
     * 获取token
     * @return string
     * @author zxqc2018
     */
    public function getAccessToken()
    {
        $path = '/cgi-bin/token';
        $query = [
            'grant_type' => 'client_credential',
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret'],
        ];

        $res = $this->getResponse($path, $query);

        return $res['access_token'] ?? '';
    }

    /**
     * 获取jsToken
     * @param $accessToken
     * @return string
     * @author zxqc2018
     */
    public function getJsTicket($accessToken)
    {
        $path = '/cgi-bin/ticket/getticket';
        $query = [
            'access_token' => $accessToken,
            'type' => 'jsapi',
        ];
        $res = $this->getResponse($path, $query, 'GET');
        return $res['ticket'] ?? '';
    }

    /**
     * 发送消息
     * @param string $accessToken
     * @param array $postData
     * @return ResultData
     * @author zxqc2018
     */
    public function sendTempMsg($accessToken, $postData)
    {
        $path = '/cgi-bin/message/template/send?access_token=' . $accessToken;
        $res = $this->getResponse($path, [], 'POST', json_encode($postData));

        return $res;
    }

    public function qrcode($accessToken, $postData)
    {
        $path = '/cgi-bin/qrcode/create?access_token=' . $accessToken;
        $res = $this->getResponse($path, [], 'POST', json_encode($postData));
        return $res;
    }

    public function getWeChatUserInfoByOpenid($accessToken, $openid)
    {
        $path = '/cgi-bin/user/info';
        $query = [
            'access_token' => $accessToken,
            'openid' => $openid,
            'lang' => 'zh_CN',
        ];
        $res = $this->getResponse($path, $query, 'GET', []);
        return $res;
    }

    public function getOpenidByCode($code)
    {
        $path = "/sns/oauth2/access_token?appid={$this->config['appid']}&secret={$this->config['secret']}&code={$code}&grant_type=authorization_code";
        $res = $this->getResponse($path, [], 'GET');
        return $res;
    }

    public function getWeChatUserInfoByToken($accessToken, $openid)
    {
        $path = "/sns/userinfo?access_token={$accessToken}&openid={$openid}&lang=zh_CN";
        $res = $this->getResponse($path, [], 'GET', []);
        return $res;
    }

    /**
     * 微信请求返回值默认处理
     * @param mixed $wechatResponseData 微信服务器返回数组
     * @param string $defaultErrorStr 错误默认提示字符串
     * @return \App\Service\ResultData
     * @author zxqc2018
     */
    public function responseDataProcess($wechatResponseData, $defaultErrorStr = '请求失败')
    {
        if ($wechatResponseData instanceof ResultData) {
            if ($wechatResponseData->getCode() > 0) {
                return $wechatResponseData;
            } else {
                $wechatResponseData = $wechatResponseData->getData();
            }
        }
        //假如请求失败等返回非数组情况
        if (!is_array($wechatResponseData)) {
            return CommonUtil::resultData([],ErrorCode::ERROR_COMMON_WECHAT_ERROR, $defaultErrorStr);
        }

        //处理正常返回
        if (!empty($wechatResponseData['errcode'])) {
            return CommonUtil::resultData([],ErrorCode::ERROR_COMMON_WECHAT_ERROR, $wechatResponseData['errmsg'] ?? $defaultErrorStr);
        }

        return CommonUtil::resultData($wechatResponseData);
    }
}
