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
            $res[] = self::Obj2Array($entity);
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
    public static function Obj2Array($obj, $defaultValue = [], $toArrayMethod = 'getArray')
    {
        $res = $defaultValue;

        if (empty($obj)) {
            return $res;
        }

        if (method_exists($obj, $toArrayMethod)) {
            $res = $obj->$toArrayMethod();
        }

        return $res;
    }
}