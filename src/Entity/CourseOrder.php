<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 6:00 PM
 */

namespace App\Entity;

use App\Entity\Traits\StatusTrait;
use App\Repository\CourseOrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseOrderRepository")
 */
class CourseOrder extends GroupUserOrder
{
    public function setStatus($status)
    {
        switch ($status) {
            case self::CREATED: return $this->setCreated();
            case self::CANCELLED: return $this->setCancelled();
            case self::DELIVERED: return $this->setRegistered();
        }
    }

    /**
     * @return bool
     */
    public function isCourseOrder()
    {
        return true;
    }

    /**
     * @return $this
     */
    public function setRegistered()
    {
        if ($this->isRegistered())
            return $this;

       $this->status = GroupUserOrder::DELIVERED;
       $this->setUpdatedAt();
       $this->setPaid();

       $this->getCourse()->registerStudent($this->getUser());

       return $this;
    }

    /**
     * @return Course|null
     */
    public function getCourse() {
        return $this->getProduct()->getCourse();
    }

    /**
     * @return bool
     */
    public function isRegistered() {
        return $this->getStatus() == GroupUserOrder::DELIVERED;
    }

}