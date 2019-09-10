<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Service\Util\CommonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseInspectorRepository")
 */
class CourseInspector implements Dao
{
    use IdTrait,CreatedAtTrait,UpdatedAtTrait;

    /**
     * @var User|null $ownerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @var null|Course
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", cascade={"persist"}, inversedBy="courseInspector")
     */
    private $course;

    /**
     * @var text
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inspectorName;

    /**
     * @var int
     * @ORM\Column( type="integer", nullable=true)
     */
    private $inspectorStartDate;

    /**
     * @var int
     * @ORM\Column( type="integer", nullable=true)
     */
    private $inspectorEndDate;


    public function __construct()
    {
        $this->setUpdatedAt();
        $this->setCreatedAt();
    }

    /**
     * @return int|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int|null $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return int|null
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param int|null $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
    }

    /**
     * @return int|null
     */
    public function getInspectorName()
    {
        return $this->inspectorName;
    }

    /**
     * @param int|null $inspectorName
     */
    public function setInspectorName($inspectorName)
    {
        $this->inspectorName = $inspectorName;
    }

    /**
     * @return int|null
     */
    public function getInspectorStartDate()
    {
        return $this->inspectorStartDate;
    }

    /**
     * @param int|null $inspectorStartDate
     */
    public function setInspectorStartDate($inspectorStartDate)
    {
        $this->inspectorStartDate = $inspectorStartDate;
    }

    /**
     * @return int|null
     */
    public function getInspectorEndDate()
    {
        return $this->inspectorEndDate;
    }

    /**
     * @param int $inspectorEndDate
     */
    public function setInspectorEndDate($inspectorEndDate)
    {
        $this->inspectorEndDate = $inspectorEndDate;
    }

    /**
     * @return string
     */
    public function getInspectorStartDateFormatted() {
        return date(DAO::DATE_FORMAT, $this->inspectorStartDate);
    }

    /**
     * @return string
     */
    public function getInspectorEndDateFormatted() {
        return date(DAO::DATE_FORMAT, $this->inspectorEndDate);
    }

    public function getShowInspector() {
        return false;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->getLittleArray(),
            'course' => $this->getCourse()->getLittleArray(),
            'inspectorName' => $this->getInspectorName(),
            'startDate' => $this->getInspectorStartDateFormatted(),
            'endDate' => $this->getInspectorEndDateFormatted(),
        ];
    }
}
