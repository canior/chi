<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserParentLogRepository")
 */
class UserParentLog implements Dao
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userParentLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var User|null $parentUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parentUser;

    /**
     * @var ShareSource|null
     * @ORM\ManyToOne(targetEntity="App\Entity\ShareSource")
     */
    private $shareSource;


    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $memo;


    /**
     * @param User $user
     * @param User|null $parentUser
     * @param ShareSource|null $shareSource
     * @param string|null $memo
     * @return UserParentLog
     */
    public static function factory(User $user, User $parentUser = null, ShareSource $shareSource = null, $memo = null) {
        $userParentLog = new UserParentLog();
        $userParentLog->setUser($user);
        $userParentLog->setParentUser($parentUser);
        $userParentLog->setShareSource($shareSource);
        $userParentLog->setMemo($memo);

        return $userParentLog;
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
     * @return User|null
     */
    public function getParentUser(): ?User
    {
        return $this->parentUser;
    }

    /**
     * @param User|null $parentUser
     */
    public function setParentUser(?User $parentUser): void
    {
        $this->parentUser = $parentUser;
    }

    /**
     * @return ShareSource|null
     */
    public function getShareSource(): ?ShareSource
    {
        return $this->shareSource;
    }

    /**
     * @param ShareSource|null $shareSource
     */
    public function setShareSource(?ShareSource $shareSource): void
    {
        $this->shareSource = $shareSource;
    }

    /**
     * @return null|string
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @param null|string $memo
     */
    public function setMemo(?string $memo): void
    {
        $this->memo = $memo;
    }

    public function __toString()
    {
        return $this->getUser() . ' ' . $this->getMemo();
    }

}
