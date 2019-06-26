<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use  App\Entity\Follow;
/**
 * @ORM\Entity(repositoryClass="App\Repository\FollowRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"course" = "FollowCourseMeta", "teacher" = "FollowTeacherMeta"})
 */
abstract class Follow implements Dao
{
    const COURSE = 'course';
    const TEACHER = 'teacher';

    public static $types = [
        self::COURSE => '课程',
        self::TEACHER => '讲师'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;


    /** 
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="Course") 
     * @ORM\JoinColumn(name="data_id", referencedColumnName="id", nullable=false)
     */
    protected $dataId;


    /**
     * @var User|null $ownerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;


    public function getId()
    {
        return $this->id;
    }
    
    public function getDataId()
    {
        return $this->dataId;
    }

    public function setDataId(string $dataId): self
    {
        $this->dataId = $dataId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }



    public abstract function isCourse();

    public abstract function isTeacher();

}
