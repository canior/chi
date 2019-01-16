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
     * @var string
     * @ORM\Column(type="string")
     */
    private $memo;

    /**
     * UpgradeUserOrderPayment constructor.
     * @param UpgradeUserOrder $upgradeUserOrder
     * @param float $amount
     * @param string $memo
     * @return UpgradeUserOrder
     */
    public static function factory(UpgradeUserOrder $upgradeUserOrder, $amount, $memo = null) {
        $upgradeUserOrderPayment = new UpgradeUserOrderPayment();
        $upgradeUserOrderPayment->setUpgradeUserOrder($upgradeUserOrder);
        $upgradeUserOrderPayment->setAmount($amount);
        $upgradeUserOrderPayment->setMemo($memo);
        return $upgradeUserOrder;
    }

    public function __construct()
    {
        $this->setCreatedAt();
        $this->setAmount(0);
    }

    /**
     * @return UpgradeUserOrder|null
     */
    public function getUpgradeUserOrder(): ?UpgradeUserOrder
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

    /**
     * @return string|null
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @param string|null  $memo
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    /**
     * @return array
     */
    public function getArray() {
        return [
            'id' => $this->getId(),
            'upgradeUserOrderId' => $this->getUpgradeUserOrder()->getId(),
            'amount' => $this->getAmount(),
            'memo' => $this->getMemo(),
            'createdAt' => $this->getCreatedAt(true)
        ];
    }
}