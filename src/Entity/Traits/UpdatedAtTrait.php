<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-27
 * Time: 19:11
 */

namespace App\Entity\Traits;

trait UpdatedAtTrait
{
    /**
     * @var int
     * @ORM\Column(name="updated_at", type="integer", nullable=true)
     */
    private $updatedAt;

    /**
     * Get updatedAt
     *
     * @param bool $formatted
     * @return int
     */
    public function getUpdatedAt($formatted = true)
    {
        if ($formatted) {
            return $this->updatedAt ? date(self::DATETIME_FORMAT, $this->updatedAt) : null;
        }
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAtFormatted() {
        return $this->getUpdatedAt(true);
    }

    /**
     * Set updatedAt
     *
     * @param int $updatedAt|null
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt = null) : self
    {
        if ($updatedAt == null) {
            $this->updatedAt = time();
        } else {
            $this->updatedAt = $updatedAt;
        }
        return $this;
    }
}