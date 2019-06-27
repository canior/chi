<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/28
 * Time: 00:41
 */

namespace App\Service\WeChat\OfficialAccount;



use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;

/**
 * 微信处理类
 * Class WeChatProcess
 * @package App\Logics\Wechat
 * @author zxqc2018
 */
class WeChatProcess
{
    const GZH_PROCESS = 'gzh';
    /**
     * @var WeChatProcessInterface
     */
    protected $weChatProcess;

    /**
     * WeChatProcess constructor.
     * @param string $type 公众号类型
     */
    public function __construct($type = null)
    {
        $this->weChatProcess = $this->makeWechatProcess($type);
    }

    /**
     * 生成微信对应的处理类
     * @param string $type 类型
     * @return WeChatProcessInterface
     * @author zxqc2018
     */
    protected function makeWeChatProcess($type)
    {
        switch ($type) {
            case self::GZH_PROCESS:
                return  new GzhWeChatProcess();
                break;
        }
        CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_WX_PROCESS_NOT_EXISTS, []);
    }

    /**
     * 获取token  调试用
     * @param bool $refresh
     * @return string
     */
    public function getToken($refresh=false){
        return $this->weChatProcess->getWeChatApp()->getAccessTokenService()->getToken($refresh);
    }

    /**
     * 授权回跳页面保存openId
     * @param bool $onlyOpenid 是否只是取得opneid false 则根据access_token再取得用户信息
     * @param string|null $code 授权code
     * @return mixed
     * @author zxqc2018
     */
    public function getOpenidByCode($onlyOpenid = true, $code = null)
    {
        return $this->weChatProcess->getOpenidByCode($onlyOpenid, $code);
    }


    /**
     * 获取微信js-sdk配置
     * @param string $url 调用js的url
     * @return array
     * @author zxqc2018
     */
    public function getJsBuildConfig($url = '')
    {
        $res = $this->weChatProcess->getWeChatApp()->getJsConfig($url);
        unset($res['debug'], $res['beta'], $res['jsApiList'], $res['url']);
        return $res;
    }
}
