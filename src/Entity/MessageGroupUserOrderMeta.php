<?php
/**
 * Created by PhpStorm.
 * User: Jeff
 * Date: 2019-06-26
 * Time: 1:07 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Message;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageGroupUserOrderMetaRepository")
 */
class MessageGroupUserOrderMeta extends Message
{


    /**
     * @return array
     */
    public function getArray()
    {
        return [
        ];
    }
}