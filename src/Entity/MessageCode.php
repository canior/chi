<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageCodeRepository")
 */
class MessageCode implements Dao
{
    const LOGIN = 'login';
    const FORGET = 'forget';

    public static $types = [
        self::LOGIN => '用户登陆',
        self::FORGET => '忘记密码'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var string|null
     */
    private $type;
}
