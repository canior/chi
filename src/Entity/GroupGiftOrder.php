<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 6:27 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupGiftOrderRepository")
 */
class GroupGiftOrder extends GroupOrder
{
    public function __construct() {
        parent::__construct();
    }
}