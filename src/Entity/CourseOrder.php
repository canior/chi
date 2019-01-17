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
     * @return $this|GroupUserOrder
     */
    public function setCreated() {
        $this->status = GroupUserOrder::CREATED;
        $this->setUpdatedAt();
        return $this;
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
     * @return $this|GroupUserOrder
     */
    public function setCancelled()
    {
        $this->status = GroupUserOrder::CANCELLED;
        $this->setUpdatedAt();
        return $this;
    }

    /**
     * @return bool
     */
    public function isRegistered() {
        return $this->getStatus() == GroupUserOrder::DELIVERED;
    }

    /**
     * @return $this|GroupUserOrder
     */
    public function setPaid() {
        if ($this->isPaid())
            return $this;

        $this->paymentStatus = GroupUserOrder::PAID;
        $this->setUpdatedAt();
        $this->setRegistered();
        return $this;
    }

    /**
     * @return $this|GroupUserOrder
     */
    public function setUnPaid()
    {
        $this->paymentStatus = GroupUserOrder::UNPAID;
        $this->setUpdatedAt();
        return $this;
    }




}