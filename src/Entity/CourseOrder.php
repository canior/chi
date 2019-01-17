<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-17
 * Time: 6:00 PM
 */

namespace App\Entity;

use App\Repository\CourseOrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseOrderRepository")
 */
class CourseOrder extends GroupUserOrder
{
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
        $this->setStatus(GroupUserOrder::CREATED);
        $this->setUpdatedAt();
        return $this;
    }

    /**
     * @return $this
     */
    public function setRegistered()
    {
       $this->setStatus(GroupUserOrder::DELIVERED);
       $this->setUpdatedAt();
       if ($this->isUnPaid()) {
           $this->setPaid();
       }

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
        $this->setStatus(GroupUserOrder::CANCELLED);
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
        $this->setPaymentStatus(GroupUserOrder::PAID);
        $this->setUpdatedAt();
        $this->setRegistered();
        return $this;
    }

    /**
     * @return $this|GroupUserOrder
     */
    public function setUnPaid()
    {
        $this->setPaymentStatus(GroupUserOrder::UNPAID);
        $this->setUpdatedAt();
        return $this;
    }




}