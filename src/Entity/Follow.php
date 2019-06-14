<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FollowRepository")
 */
class Follow implements Dao
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
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dataId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $type;

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
    
    public function getDataId(): ?string
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
}
