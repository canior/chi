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
     * Set createdAt
     *
     * @param int $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        if (is_string($createdAt)) {
            $createdAt = strtotime($createdAt);
        }
        $this->createdAt = $createdAt;
        return $this;
    }
}