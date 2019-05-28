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
     * @param int $statusCode http状态码
     * @param string $msg 错误码描述
     * @author zxqc2018
     * @return ResultData
     */
    public static function resultData($data = [], $code = 0, $statusCode = 200, $msg = 'success'): ResultData
    {
        return new ResultData($data, $code, $msg, $statusCode);
    }
}