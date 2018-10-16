<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-28
 * Time: 07:33
 */

namespace App\Entity\Traits;

trait UnitPriceTrait
{
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $unitPrice;

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    public function setUnitPrice($unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }
}