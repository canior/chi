<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageCodeRepository")
 */
class MessageCode implements Dao
{
    const LOGIN = 'login';
    const FORGET = 'forget';
    const UPDATE_INFO = 'update_info';

    public static $types = [
        self::LOGIN => '用户登陆',
        self::UPDATE_INFO => '编辑资料',
        self::FORGET => '忘记密码'
    ];

    use IdTrait, CreatedAtTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $type;

    /**
     * GroupUserOrderRewards constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(time());
    }


    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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
}
