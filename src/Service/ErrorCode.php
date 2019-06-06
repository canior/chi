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
    //-------课程---------------------------------------

    //-------产品---------------------------------------

    //-------订单---------------------------------------

    //-------支付---------------------------------------
    const ERROR_PAY_COMMON = 16001;
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
            self::ERROR_LOGIN_USER_NOT_FIND => '用户不存在',
            self::ERROR_LOGIN_USERNAME_OR_PASSWORD_ERROR => '用户名或密码不不正确',
            self::ERROR_LOGIN_PHONE_OR_CODE_ERROR => '手机号或动态码不不正确',
            self::ERROR_GREATER_THEN_ACCOUNT => '提现金额大于余额',
            self::ERROR_GREATER_COUNT => '提现金额必须大于0',
            self::ERROR_HAD_FOLLOW => '你已经关注了',
            self::ERROR_FOLLOW_NOTFIND => '关注不存在或已经取消',
            self::ERROR_PAY_COMMON => '支付异常',
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