<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-13
 * Time: 6:44 PM
 */

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserLogRepository")
 */
class UserLog
{
    use IdTrait;
    use CreatedAtTrait;

    const INFO = 'INFO';
    const ERROR = 'ERROR';

    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private $log;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private $backtrace;


    /**
     * @param User $user
     * @param $type
     * @param $log
     * @param string|null $backtrace
     * @return UserLog
     */
    public static function factory(User $user, $type, $log, $backtrace = null) {
        $userLog = new UserLog();
        $userLog->setUser($user);
        $userLog->setType($type);
        $userLog->setLog($log);
        $userLog->setBacktrace($backtrace);
        return $userLog;
    }

    public function __construct() {
        $this->setCreatedAt();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getLog(): ?string
    {
        return $this->log;
    }

    /**
     * @param null|string $log
     */
    public function setLog(?string $log): void
    {
        $this->log = $log;
    }

    /**
     * @return null|string
     */
    public function getBacktrace(): ?string
    {
        return $this->backtrace;
    }

    /**
     * @param null|string $backtrace
     */
    public function setBacktrace(?string $backtrace): void
    {
        $this->backtrace = $backtrace;
    }


}