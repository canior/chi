<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-27
 * Time: 18:59
 */

namespace App\Entity\Traits;

trait CreatedAtTrait
{
    /**
     * @var int
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $createdAt;

    /**
     * Get createdAt
     *
     * @param bool $formatted
     * @return int
     */
    public function getCreatedAt($formatted = true)
    {
        if ($formatted) {
            return $this->createdAt ? date(self::DATETIME_FORMAT, $this->createdAt) : null;
        }
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getCreatedAtFormatted() {
        return $this->getCreatedAt(true);
    }

    /**
     * @return string
     */
    public function getCreatedAtDateFormatted() {
        return date(self::DATE_FORMAT, $this->createdAt);
    }

    /**
     * Set createdAt
     *
     * @param int $createdAt | null
     *
     * @return $this
     */
    public function setCreatedAt($createdAt = null)
    {
        if ($createdAt == null) {
            $this->createdAt = time();
        } else {
            $this->createdAt = $createdAt;
        }

        return $this;
    }
}