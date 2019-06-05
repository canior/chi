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
     * @ORM\Column(type="integer")
     * @var string|null
     */
    private $userId;


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

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
