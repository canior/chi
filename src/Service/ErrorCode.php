<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 9:16
 */

namespace App\Service;


/**
 * 错误定义类
 * Class ErrorCode
 * @package App\Service
 * @author zxqc2018
 */
class ErrorCode
{
    const ERROR_COMMON_UNKNOWN_ERROR = 10000;

    //-------通用---------------------------------------
    const ERROR_UPLOAD_FILE_NOT_EXISTS = 11001;
    const ERROR_UPLOAD_FILE_SAVE = 11002;
    const ERROR_PARAM_NOT_ALL_EXISTS = 11003;
    const ERROR_SMS_CLIENT_INIT = 11004;
    const ERROR_SMS_SEND_RESPONSE = 11005;
    const ERROR_COMMON_WECHAT_ERROR = 11006;
    const ERROR_COMMON_NETWORK_ERROR = 11007;
    //-------认证---------------------------------------
    const ERROR_TOKEN_INVALID = 12001;
    const ERROR_TOKEN_AUTH_FAILURE = 12002;
    const ERROR_TOKEN_AUTH_NOT_FOUND = 12003;
    const ERROR_LOGIN_USER_NOT_FIND = 12004;
    const ERROR_LOGIN_USERNAME_OR_PASSWORD_ERROR = 12005;
    const ERROR_LOGIN_PHONE_OR_CODE_ERROR = 12006;
    const ERROR_GREATER_THEN_ACCOUNT = 12007;
    const ERROR_GREATER_COUNT = 12008;
    const ERROR_HAD_FOLLOW = 12009;
    const ERROR_FOLLOW_NOTFIND = 12010;
    const ERROR_MESSAGE_NOT_FIND = 12011;
    const ERROR_WX_OPENID_LOGIN = 12012;
    const ERROR_LOGIN_CODE_TIMEOUT = 12013;
    const ERROR_WX_TOKEN_GET = 12014;
    const ERROR_WX_JS_TOKEN_GET = 12015;
    const ERROR_WX_PROCESS_NOT_EXISTS = 12016;
    const ERROR_WX_OPENID_WITH_CODE = 12017;
    const ERROR_PHONE_HAD_REGISTER = 12018;
    const ERROR_PLOCE_LOGIN_BY_PHONE = 12019;
    const ERROR_UNIONID_HAD_REGISTER = 12020;
    const ERROR_SIGN = 12021;
    const ERROR_UPGRADE_CODE_NOT_EXISTS = 12022;


    //-------课程---------------------------------------
    const ERROR_CATEGORY_NOT_EXISTS = 13001;
    const ERROR_COURSE_NOT_EXISTS = 13002;
    const ERROR_PARTNER_USER_REF = 13003;
    const ERROR_PARTNER_RECOMMEND_STOCK_EMPTY = 13004;
    const ERROR_NOT_PARTNER_UP_LEVEL = 13005;
    const ERROR_PARTNER_UP_COURSE_NOT_SYSTEM = 13006;
    const ERROR_USER_ALREADY_SYSTEM = 13007;
    //-------产品---------------------------------------
    const ERROR_PRODUCT_NOT_EXISTS = 14001;
    //-------订单---------------------------------------
    const ERROR_ORDER_TABLE_CREATE_FAIL = 15001;
    const ERROR_ADDRESS_NOT_EXISTS = 15002;


    //-------支付---------------------------------------
    const ERROR_PAY_COMMON = 16001;
    const ERROR_WX_PAY_CONFIG = 16002;
    const ERROR_ALI_PAY_CONFIG = 16003;
    const ERROR_PAY_HTTP_RESPONSE = 16004;
    const ERROR_CONFIG_SET_GET = 16005;
    const ERROR_WX_PAY_TRANSFER = 16006;
    const ERROR_WX_PAY_TRANSFER_BANK = 16007;
    const ERROR_WX_PAY_PREPAY_ID = 16008;
    const ERROR_PAY_ORDER_ID_NO_EXISTS = 16009;
    const ERROR_PAY_CHANNEL_NO_EXISTS = 16010;
    const ERROR_PAY_NOTIFY = 16011;
    const ERROR_PAY_ORDER_ALREADY_WAIT = 16012;
    const ERROR_UNLOCK_CATEGORY_NOT_PRIVILEGE = 16013;
    const ERROR_ORDER_ALREADY_PAY = 16014;
    const ERROR_NOTIFY_RAW_NOT_ALLOW = 16015;
    const ERROR_NOTIFY_VERIFY_SIGN = 16016;
    const ERROR_NOTIFY_TYPE = 16017;
    const ERROR_COURSE_ALREADY_PAY = 16018;
    const ERROR_COURSE_CATEGORY_ALREADY_PAY = 16019;
    const ERROR_GZH_PAY_ID_NOT_EXISTS = 16020;
    /**
     * 错误消息集合
     * @return array
     * @author zxqc2018
     */
    public static function  getMessages()
    {
        return [
            self::ERROR_COMMON_UNKNOWN_ERROR => '未知错误',
            self::ERROR_UPLOAD_FILE_NOT_EXISTS => '文件不存在',
            self::ERROR_UPLOAD_FILE_SAVE => '文件保存失败',
            self::ERROR_TOKEN_INVALID => 'token无效',
            self::ERROR_TOKEN_AUTH_FAILURE => 'token认证失败',
            self::ERROR_TOKEN_AUTH_NOT_FOUND => 'token不存在',
            self::ERROR_LOGIN_USER_NOT_FIND => '用户不存在,请使用微信登陆',
            self::ERROR_LOGIN_USERNAME_OR_PASSWORD_ERROR => '用户名或密码不不正确',
            self::ERROR_LOGIN_PHONE_OR_CODE_ERROR => '手机号或动态码不正确',
            self::ERROR_GREATER_THEN_ACCOUNT => '提现金额大于余额',
            self::ERROR_GREATER_COUNT => '提现金额必须大于0',
            self::ERROR_HAD_FOLLOW => '你已经关注了',
            self::ERROR_FOLLOW_NOTFIND => '关注不存在或已经取消',
            self::ERROR_PAY_COMMON => '支付异常',
            self::ERROR_MESSAGE_NOT_FIND => '消息不存在',
            self::ERROR_WX_PAY_CONFIG => '微信支付配置异常',
            self::ERROR_ALI_PAY_CONFIG => '阿里支付配置异常',
            self::ERROR_PAY_HTTP_RESPONSE => '支付请求失败',
            self::ERROR_CONFIG_SET_GET => '支付配置设置错误',
            self::ERROR_WX_PAY_TRANSFER => '微信支付企业转账错误',
            self::ERROR_WX_PAY_TRANSFER_BANK => '微信支付转账银行卡错误',
            self::ERROR_PARAM_NOT_ALL_EXISTS => '参数错误',
            self::ERROR_WX_PAY_PREPAY_ID => '微信统一支付id获取失败',
            self::ERROR_PAY_ORDER_ID_NO_EXISTS => '支付订单ID不存在',
            self::ERROR_PAY_CHANNEL_NO_EXISTS => '支付渠道不存在',
            self::ERROR_PRODUCT_NOT_EXISTS => '产品不存在',
            self::ERROR_PAY_NOTIFY => '回调处理失败',
            self::ERROR_PAY_ORDER_ALREADY_WAIT => '订单已经待支付',
            self::ERROR_SMS_CLIENT_INIT => '短信客户端初始化失败',
            self::ERROR_SMS_SEND_RESPONSE => '短信发送失败',
            self::ERROR_CATEGORY_NOT_EXISTS => '课程类别不存在',
            self::ERROR_UNLOCK_CATEGORY_NOT_PRIVILEGE => '无权限解锁该系列',
            self::ERROR_COMMON_WECHAT_ERROR => '微信端错误',
            self::ERROR_COMMON_NETWORK_ERROR => '网络请求错误',
            self::ERROR_WX_OPENID_LOGIN => '微信登陆失败',
            self::ERROR_ORDER_ALREADY_PAY => '订单已经支付',
            self::ERROR_ORDER_TABLE_CREATE_FAIL => '桌号生成失败',
            self::ERROR_ADDRESS_NOT_EXISTS => '收获地址不存在',
            self::ERROR_LOGIN_CODE_TIMEOUT => '短信验证码已过期',
            self::ERROR_NOTIFY_RAW_NOT_ALLOW => '异步通知消息不合法',
            self::ERROR_NOTIFY_VERIFY_SIGN => '异步通知验签失败',
            self::ERROR_NOTIFY_TYPE => '通知方式不支持',
            self::ERROR_COURSE_ALREADY_PAY => '课程或者活动已经购买',
            self::ERROR_COURSE_NOT_EXISTS => '课程或者活动不存在',
            self::ERROR_COURSE_CATEGORY_ALREADY_PAY => '系列课程已经解锁',
            self::ERROR_WX_TOKEN_GET => '获取微信token失败',
            self::ERROR_WX_JS_TOKEN_GET => '获取微信jsToken失败',
            self::ERROR_WX_PROCESS_NOT_EXISTS => '微信公众号处理类不存在',
            self::ERROR_WX_OPENID_WITH_CODE => '微信公code获取openid失败',
            self::ERROR_GZH_PAY_ID_NOT_EXISTS => '微信公众号openid不存在',
            self::ERROR_PARTNER_USER_REF => '合伙人|分院与用户身份确认失败',
            self::ERROR_PARTNER_RECOMMEND_STOCK_EMPTY => '合伙人|分院名额用完',
            self::ERROR_NOT_PARTNER_UP_LEVEL => '合伙人|分院身份确认失败',
            self::ERROR_PARTNER_UP_COURSE_NOT_SYSTEM => '确认身份订单不合法',
            self::ERROR_USER_ALREADY_SYSTEM => '用户已经是系统学员',
            self::ERROR_PHONE_HAD_REGISTER => '手机号已经存在',
            self::ERROR_PLOCE_LOGIN_BY_PHONE => '请用手机号登陆后再绑定微信',
            self::ERROR_UNIONID_HAD_REGISTER => '微信已经绑定其他手机号',
            self::ERROR_SIGN => '非法签名',
            self::ERROR_UPGRADE_CODE_NOT_EXISTS => '升级码不存在或已使用',
        ];
    }

    /**
     * 获取错误描述
     * @param $code
     * @return mixed
     * @author zxqc2018
     */
    public static function getMessage($code)
    {
        $data = static::getMessages();
        return $data[$code] ?: '未知错误';
    }
}