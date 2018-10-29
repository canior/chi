<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductStatisticsRepository")
 */
class ProductStatistics implements Dao
{
    use IdTrait;

    /**
     * @ORM\Column(type="integer")
     */
    private $reviewsNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderNum;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $orderAmountTotal;

    /**
     * @ORM\Column(type="integer")
     */
    private $buyersNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $returnUsersNum;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $returnUsersRate;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="integer")
     */
    private $month;

    /**
     * @ORM\Column(type="integer")
     */
    private $day;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="productStatistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * ProductStatistics constructor.
     */
    public function __construct()
    {
        $this->setReviewsNum(0);
        $this->setOrderNum(0);
        $this->setBuyersNum(0);
        $this->setReturnUsersNum(0);
        $this->setOrderAmountTotal(0);
        $this->setReturnUsersRate(0);
        $this->setYear(date('Y'));
        $this->setMonth(date('m'));
        $this->setDay(date('d'));
    }

    public function getReviewsNum(): ?int
    {
        return $this->reviewsNum;
    }

    public function setReviewsNum(int $reviewsNum): self
    {
        $this->reviewsNum = $reviewsNum;

        return $this;
    }

    public function getOrderNum(): ?int
    {
        return $this->orderNum;
    }

    public function setOrderNum(int $orderNum): self
    {
        $this->orderNum = $orderNum;

        return $this;
    }

    public function getOrderAmountTotal()
    {
        return $this->orderAmountTotal;
    }

    public function setOrderAmountTotal($orderAmountTotal): self
    {
        $this->orderAmountTotal = $orderAmountTotal;

        return $this;
    }

    public function getBuyersNum(): ?int
    {
        return $this->buyersNum;
    }

    public function setBuyersNum(int $buyersNum): self
    {
        $this->buyersNum = $buyersNum;

        return $this;
    }

    public function getReturnUsersNum(): ?int
    {
        return $this->returnUsersNum;
    }

    public function setReturnUsersNum(int $returnUsersNum): self
    {
        $this->returnUsersNum = $returnUsersNum;

        return $this;
    }

    public function getReturnUsersRate()
    {
        return $this->returnUsersRate;
    }

    public function setReturnUsersRate($returnUsersRate): self
    {
        $this->returnUsersRate = $returnUsersRate;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
