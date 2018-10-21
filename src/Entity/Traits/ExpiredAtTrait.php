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

    /**
     * @param bool $formatted
     * @return int|null
     */
    public function getExpiredAt($formatted = true): ?int
    {
        if ($formatted) {
            return $this->expiredAt ? date(self::DATETIME_FORMAT, $this->expiredAt) : null;
        }
        return $this->expiredAt;
    }

    /**
     * @param int $expiredAt|null
     * @return $this
     */
    public function setExpiredAt(?int $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}