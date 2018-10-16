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
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    const ACTIVE = 'active';
    const DELETED = 'deleted';

    const VIEWED = 'viewed';
    const BOUGHT = 'bought';

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';

    const CANCELLED = 'cancelled';
    const COMPLETED = 'completed';

    const PAY = 'pay';
    const CHARGE = 'charge';
    const REFUND_PAY = 'refund_pay';
    const REFUND_CHARGE = 'refund_charge';

    const CONFIRMED = 'confirmed';

    const PAID = 'paid';
    const UNPAID = 'unpaid';

    const WX = 'wx';
    const CASH = 'cash';
    const COUPON = 'coupon';
    const RED = 'red';

    const PROCESSING = 'PROCESSING';
    const ERROR = 'ERROR';

    const RUSH_DAY = 'rush_day'; // 高峰日为周五周六周日
    const RUSH_HOUR = 'rush_hour'; // 高峰时段
}