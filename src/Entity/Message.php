<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message implements Dao
{
    use IdTrait, CreatedAtTrait;

    /**
     * @var string|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $isRead;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $content;

    /**
     * @var User|null $ownerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;



    const TYPE_GROUP_USER_ORDER = 'group_user_order';
    const TYPE_USER = 'user';
    const TYPE_FOLLOW = 'follow';

    public static $dateTypes = [
        self::TYPE_GROUP_USER_ORDER => '订单类消息',
        self::TYPE_USER => '用户类消息',
        self::TYPE_FOLLOW => '关注类消息',
    ];

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $dataType;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dataId;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $expansionData;

    /**
     * GroupUserOrderRewards constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
        $this->setIsRead(0);
    }
    
    public function getIsRead(): ?string
    {
        return $this->isRead;
    }

    public function setIsRead(string $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return User|null
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param DataType|null $dataType
     */
    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }

    /**
     * @return User|null
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * @param DataId|null $dataId
     */
    public function setDataId(string $dataId): void
    {
        $this->dataId = $dataId;
    }

    /**
     * @return User|null
     */
    public function getExpansionData()
    {
        return $this->expansionData;
    }

    /**
     * @param ExpansionData|null $expansionData
     */
    public function setExpansionData(string $expansionData): void
    {
        $this->expansionData = $expansionData;
    }


    public function getArray() : array {
        return [
            'id' => $this->getId(),
            'isRead' => $this->getIsRead(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'user' => $this->getUser()->getArray(),
            'dataType' => $this->getDataType(),
            'dataId' => $this->getDataId(),
            'expansionData' => $this->getExpansionData(),
            'user' => $this->getUser()->getArray(),
        ];
    }
}
