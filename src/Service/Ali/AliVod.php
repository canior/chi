<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-04-11
 * Time: 4:19 PM
 */
namespace  App\Service\Ali;

use AlibabaCloud\Client\Result\Result;

class AliVod
{
    /**
     * @param $playInfo
     * @return string
     */
    public static function getVideoUrl($playInfo)
    {
        return $playInfo['PlayInfoList']['PlayInfo'][0]['PlayURL'];
    }

    /**
     * @param $playInfo
     * @return string
     */
    public static function getVideoImageUrl($playInfo)
    {
        return $playInfo['VideoBase']['CoverURL'];
    }

    /**
     * @param $playInfo
     * @return string
     */
    public static function getVideoExpiresAt($playInfo)
    {
        return self::getBetween(self::getVideoImageUrl($playInfo), 'Expires=', '&');
    }

    private static function getBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
