<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-18
 * Time: 2:39 PM
 */

namespace App\Service;

use Monolog\Logger as Monolog;

/**
 * 用代码重构LOG给Entity使用
 * @package App\Service
 */
class Logger
{
    /**
     * @param $message
     * @param array $context
     */
    public static  function info($message, array $context = array()) {
        $logger = new Monolog('channel-name');
        $logger->info($message, $context);
    }

}