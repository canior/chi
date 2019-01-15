<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-27
 * Time: 20:48
 */

namespace App\Entity\Traits;

trait StatusTrait
{
    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     */
    private $status;

    /**
     * Get status
     *
     * @return string
     */
    private function getStatus() : string
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status) : self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusText() : string
    {
        return isset(self::$statuses) && isset(self::$statuses[$this->status]) ? self::$statuses[$this->status] : $this->status;
    }
}