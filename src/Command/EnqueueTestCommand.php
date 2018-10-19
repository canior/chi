<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-19
 * Time: 15:37
 */

namespace App\Command;

use App\Command\Traits\SerializerTrait;

class EnqueueTestCommand implements SerializableCommandInterface
{
    use SerializerTrait;
}