<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 8:09 PM
 */

namespace App\Entity;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\UpgradeUserOrder;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UpgradeUserOrderPaymentRepository")
 */

class UpgradeUserOrderPayment implements Dao
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @var UpgradeUserOrder $upgradeUserOrder
     * @ORM\ManyToOne(targetEntity="App\Entity\UpgradeUserOrder", inversedBy="upgradeUserOrderPayments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $upgradeUserOrder;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @return UpgradeUserOrder
     */
    public function getUpgradeUserOrder(): UpgradeUserOrder
    {
        return $this->upgradeUserOrder;
    }

    /**
     * @param UpgradeUserOrder $upgradeUserOrder
     */
    public function setUpgradeUserOrder(UpgradeUserOrder $upgradeUserOrder): void
    {
        $this->upgradeUserOrder = $upgradeUserOrder;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}