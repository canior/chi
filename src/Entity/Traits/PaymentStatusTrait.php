<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-28
 * Time: 07:18
 */

namespace App\Entity\Traits;

trait PaymentStatusTrait
{
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $paymentStatus;

    private function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    private function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentStatusText()
    {
        return isset(self::$paymentStatuses) && isset(self::$paymentStatuses[$this->paymentStatus]) ? self::$paymentStatuses[$this->paymentStatus] : $this->paymentStatus;
    }
}