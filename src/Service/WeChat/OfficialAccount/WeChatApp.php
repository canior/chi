<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 22:35
 */

namespace App\Service\WeChat\OfficialAccount;

use App\Service\Document\WeChatDocument;
use App\Service\WeChat\OfficialAccount\Provider\AccessTokenService;

/**
 * 仿造easyWeChat写一个app类
 * Class WeChatApp
 * @package App\WeChat\OfficialAccount
 * @author zxqc2018
 */
class WeChatApp
{
    protected $config = [];

    protected $oauthService;

    protected $noticeService;

    protected $accessTokenService;

    protected $weChatDocument;
    /**
     * WeChatApp constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->weChatDocument = new WeChatDocument([
            'appid' => $this->config['app_id'],
            'secret' => $this->config['secret'],
        ]);
    }

    /**
     * @return WeChatDocument
     */
    public function getWeChatDocument(): WeChatDocument
    {
        return $this->weChatDocument;
    }


    /**
     * 获取token服务类
     * @return AccessTokenService
     * @author zxqc2018
     */
    public function getAccessTokenService()
    {
        if (!is_null($this->accessTokenService)) {
            return $this->accessTokenService;
        }

        $this->accessTokenService = new AccessTokenService($this->weChatDocument);

        return $this->accessTokenService;
    }


    public function getWeChatUserInfo($openid)
    {
        $res = $this->weChatDocument->getWeChatUserInfoByOpenid($this->getAccessTokenService()->getToken(), $openid);
        return $res;
    }

    public function getJsConfig($url)
    {
        $accToken = $this->getAccessTokenService()->getToken();
        $ticket = $this->getAccessTokenService()->getJsTicket();
        //生成随机字符串
        $randStr = '';
        $str = $ticket.$accToken;
        $strLength = strlen($str);
        for ($i=0;$i<15;$i++){
            if($i%3==0){
                $randStr.=rand();
            }
            $randStr.=$str[rand(0,$strLength)];
        }
        $randStr.=rand();
        $time = time();
        $tempSort = [
            'noncestr' => $randStr,
            'jsapi_ticket' => $ticket,
            'timestamp' => $time,
            'url' => $url
        ];

        $keyStr = array_flip($tempSort);
        //加密参数是按参数名排序，不是按值排序
        ksort($tempSort,SORT_STRING);
        $params = $tempSort;
        $shaString = '';
        foreach ($params as $key=>$val){
            if($shaString==''){
                $shaString = $keyStr[$val].'='.$val;
            }else{
                $shaString.='&'.$keyStr[$val].'='.$val;
            }
        }
        $signature = sha1($shaString);
        $jsConfig = [
            'appId'=> $this->config['app_id'],
            'timestamp'=>$time,
            'nonceStr'=>$randStr,
            'signature'=>$signature,
            //此处填写你需要调用的JS列表，比如这里是调用的微信获取地理位置
            'jsApiList'=>[
                'checkJsApi', 'openLocation','getLocation','chooseWXPay',
                'updateAppMessageShareData', 'updateTimelineShareData'
            ],
        ];

        return $jsConfig;
    }
}
