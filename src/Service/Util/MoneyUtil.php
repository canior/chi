<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/25
 * Time: 9:58
 */

namespace App\Service\Util;


use App\Entity\Subject;

/**
 *
 * Class MoneyUtil
 * @package App\Service\Util
 * @author zxqc2018
 */
class MoneyUtil
{
    /**
     * 四舍五入取小数
     * @param $number
     * @param int $decimals
     * @return string
     * @author zxqc2018
     */
    public static function formatHalfUp($number , $decimals = 2)
    {
        return number_format($number, $decimals, '.', '');
    }

    /**
     * 获取一元思维课价格[不同环境]
     * @return float
     * @author zxqc2018
     */
    public static function thinkingGeneratePrice()
    {
        if (CommonUtil::isDebug()) {
            return 0.01;
        }
        return Subject::THINKING_GENERATE_PRICE;
    }
}