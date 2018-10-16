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
     * Set updatedAt
     *
     * @param int $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        if (is_string($updatedAt)) {
            $updatedAt = strtotime($updatedAt);
        }
        $this->updatedAt = $updatedAt;
        return $this;
    }
}