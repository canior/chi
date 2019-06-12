<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/12
 * Time: 17:49
 */

namespace App\Service\Util;

/**
 * 常用时间处理类
 * Class TimeUtil
 * @package App\Service\Util
 * @author zxqc2018
 */
class TimeUtil
{
    /**
     * 时间格式化
     * @param string|integer $time
     * @param $format
     * @author zxqc2018
     * @return false|string
     */
    public static function formatTimeToStr($time, $format)
    {
        $res = '';
        if(empty($time)) {
            return $res;
        }

        if (is_numeric($time)) {
            if (strtotime(date('Y-m-d H:i:s', $time)) !== (int)$time) {
                $time = strtotime($time);
            }
        } else {
            $time = strtotime($time);
        }

        $formatTime = date($format, $time);
        if (!empty($formatTime)) {
            $res = $formatTime;
        }

        return $res;
    }
}