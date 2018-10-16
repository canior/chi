<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 08:48
 */

namespace App\Command;

interface SerializableCommandInterface extends CommandInterface
{
    /**
     * @return string
     */
    public function serialize();

    /**
     * @param $json
     * @return $this
     */
    public function deserialize($json);
}