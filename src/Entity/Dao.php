<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-27
 * Time: 18:29
 */

namespace App\Entity;

interface Dao
{
    const DATETIME_FORMAT = 'Y/m/d H:i:s';
    const DATE_FORMAT = 'Y/m/d';
    const DATETIME_START = 'Y/m/d 00:00:00';
    const DATETIME_END = 'Y/m/d 23:59:59';
}