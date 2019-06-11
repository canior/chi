<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 10:03
 */

namespace App\Service\Util;

use App\Service\ResultData;

/**
 * 常用方法
 * Class Common
 * @package App\Service\Util
 * @author zxqc2018
 */
class CommonUtil
{
    /**
     * 通用结果返回类
     * @param array $data 数据
     * @param int $code 错误码 0 默认正确
     * @param string $msg 错误码描述
     * @param int $statusCode http状态码
     * @author zxqc2018
     * @return ResultData
     */
    public static function resultData($data = [], $code = 0, $msg = 'success', $statusCode = 200): ResultData
    {
        return new ResultData($data, $code, $msg, $statusCode);
    }

    /**
     * entity对象数组转换成entity数据数组
     * @param $entityArray
     * @return array
     * @author zxqc2018
     */
    public static function entityArray2DataArray($entityArray)
    {
        $res = [];
        foreach ($entityArray as $entity) {
            $res[] = self::obj2Array($entity);
        }

        return $res;
    }


    /**
     * 调用对象方法
     * @param $obj
     * @param mixed $defaultValue 默认值
     * @param string $method 对象方法
     * @return mixed
     * @author zxqc2018
     */
    public static function invokingObjMethod($obj, $defaultValue = [], $method = 'getArray')
    {
        $res = $defaultValue;

        if (empty($obj)) {
            return $res;
        }

        if (method_exists($obj, $method)) {
            $res = $obj->$method();
        }

        return $res;
    }

    /**
     * 对象转换数组
     * @param $obj
     * @param array $defaultValue 默认值
     * @param string $toArrayMethod 对象转换数组方法
     * @return array|null
     * @author zxqc2018
     */
    public static function obj2Array($obj, $defaultValue = [], $toArrayMethod = 'getArray')
    {
        return self::invokingObjMethod($obj, $defaultValue, $toArrayMethod);
    }


    /**
     * 对象转换ID
     * @param $obj
     * @param int $defaultValue 默认值
     * @param string $toIdMethod 对象转换ID方法
     * @return int
     * @author zxqc2018
     */
    public static function obj2Id($obj, $defaultValue = 0, $toIdMethod = 'getId')
    {
        return self::invokingObjMethod($obj, $defaultValue, $toIdMethod);
    }

    /**
     * 封装explode和join
     * @param mixed $data 处理的数据
     * @param string|array $separator 分割数据 字符串 explode 处理 数组 正则处理 如  ['[3-4\s]+']
     * @param bool $retArr 是否返回数组
     * @param null|int $arrLimit 返回数组并且原数据不是数组情况下有效[数组的长度]  默认不限制
     * @return array|string
     * @author zxqc2018
     */
    public static function myExplode($data, $separator = ',', $retArr = true, $arrLimit = null)
    {
        if (is_null($data)) {
            $data = [];
        }

        if ($retArr && is_array($data)) {
            return $data;
        }

        if (!$retArr && !is_array($data)) {
            return $data;
        }
        if (!is_array($data)) {
            //数组则当正则处理
            if (is_array($separator)) {
                $separator = $separator[0];
                $data      = preg_split('#' . preg_quote($separator, '/') . '#', $data, $arrLimit);
            } else {
                //由于默认传null  explode 会当成 1处理 所以
                if (is_null($arrLimit)) {
                    $data = explode($separator, $data);
                } else {
                    $data = explode($separator, $data, $arrLimit);
                }
            }
        }

        return $retArr ? $data : join($separator, $data);
    }

    /**
     * 获取用户ip
     * @return string
     * @author zxqc2018
     */
    public static function getUserIp()
    {
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"]))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else
        {
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }

    /**
     * 是否debug模式
     * @return bool
     * @author zxqc2018
     */
    public static function isDebug()
    {
        $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
        return (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ('prod' !== $env));
    }
}