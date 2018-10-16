<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 08:50
 */

namespace App\Command;

abstract class AbstractCommandHandler
{
    /**
     * @param CommandInterface $command
     * @return mixed
     */
    public abstract function handle(CommandInterface $command);
}