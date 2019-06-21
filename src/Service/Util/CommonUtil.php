<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 10:03
 */

namespace App\Service\Util;

use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\ResultData;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

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
     * @param string $keyOrMethodStr
     * @return array
     * @author zxqc2018
     */
    public static function entityArray2DataArray($entityArray, $keyOrMethodStr = 'array')
    {
        $res = [];
        foreach ($entityArray as $entity) {
            $res[] = self::getInsideValue($entity, $keyOrMethodStr);
        }

        return $res;
    }

    /**
     * 查询数组或者对象的值,支持3层嵌套
     * @param mixed $variable ['a' => 'aa' => 'aaa' => 123]
     * @param string $keyOrMethodStr a.aa.aaa  假如对象取得属性 如 对象属性[name]  name|getName 属性名(自动拼接get方法)|对象方法名
     * @param mixed $defaultValue 默认值
     * @return mixed
     * @author zxqc2018
     */
    public static function getInsideValue($variable, $keyOrMethodStr = 'array', $defaultValue = [])
    {
        $res = $defaultValue;

        if (is_null($variable)) {
            return $res;
        }

        $isObj = is_object($variable);
        $isArray = is_array($variable);

        if (!$isObj && !$isArray) {
            return $res;
        }

        //简单获取一级数组或对象值
        $singleGetVal = function ($val, $key) use ($defaultValue){
            $res = $val;
            if (is_null($val)) {
                $res = $defaultValue;
            } else if (is_array($val)) {
                $res = $val[$key] ?? $defaultValue;
            } else if (is_object($val)){
                if (method_exists($val, $key)) {
                    $res = $val->$key();
                } else {
                    $key = 'get' . ucfirst($key);
                    if (method_exists($val, $key)) {
                        $res = $val->$key();
                    } else {
                        $res = $defaultValue;
                    }
                }
            }

            return $res;
        };

        $parts = self::myExplode($keyOrMethodStr, '.');
        switch (count($parts)) {
            case 1:
                $res = $singleGetVal($variable, $parts[0]);
                break;
            case 2:
                $res = $singleGetVal($singleGetVal($variable, $parts[0]), $parts[1]);
                break;
            default:
                $res = $singleGetVal($singleGetVal($singleGetVal($variable, $parts[0]), $parts[1]), $parts[2]);
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
    public static function obj2Array($obj, $defaultValue = null, $toArrayMethod = 'getArray')
    {
        return self::getInsideValue($obj, $toArrayMethod, $defaultValue);
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
        return self::getInsideValue($obj, $toIdMethod, $defaultValue);
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

    /**
     * 混合双向操作方法
     * @param mixed $data 处理的数据
     * @param int $optType 操作类型 1 序列化 2 json
     * @param bool $isForward 是否正操作
     * @param bool $isGrace 是否优雅处理[捕获异常]
     * @param array $extraData 额外数据
     * @return mixed
     * @author zxqc2018
     */
    public static function mixedTwoWayOpt($data, $optType = 2, $isForward = false, $isGrace = true, $extraData = [])
    {
        $defaultVal = $extraData['defaultVal'] ?? [];
        $res = $data;

        try {
            switch ($optType) {
                case 1:
                    if (!$isForward) {
                        //替换转义后的双引号
                        $data = str_replace("&quot;", '"', $data);
                        $res  = unserialize($data);
                    } else {
                        $res = serialize($data);
                    }
                    break;
                case 2:
                    if (!$isForward) {
                        $res = json_decode($data, true);
                    } else {
                        $res = json_encode($data, JSON_UNESCAPED_UNICODE);
                    }
                    break;
            }
        } catch (Throwable $e) {
            if ($isGrace) {
                $res = $defaultVal;
            } else {
                CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_COMMON_UNKNOWN_ERROR, []);
            }
        }

        //返回默认值
        if ($isGrace && is_null($res)) {
            $res = $defaultVal;
        }

        return $res;
    }

    /**
     * 请求URL是否开始于xxx
     * @param Request $request
     * @param string $prefix
     * @return bool
     * @author zxqc2018
     */
    public static function requestUrlStartsWith(Request $request, $prefix)
    {
        $res = false;

        if (!empty($request->getRequestUri()) && strpos($request->getRequestUri(), '/' . ltrim($prefix, '/')) === 0) {
            $res = true;
        }
        return $res;
    }

    /**
     * 持久entity
     * @param $entity
     * @param bool $isFlush 是否需要flush
     * @author zxqc2018
     */
    public static function entityPersist($entity, $isFlush = true)
    {
        $em = ConfigParams::getDoctrine()->getManager();
        $em->persist($entity);
        if ($isFlush) {
            $em->flush();
        }
    }
}