<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-28
 * Time: 06:34
 */

namespace App\Entity\Traits;

trait ExpiredAtTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $expiredAt;

    public function getExpiredAt(): ?int
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?int $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}