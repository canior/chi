<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandMessageRepository")
 */
class CommandMessage implements Dao
{
    use IdTrait,
        StatusTrait,
        CreatedAtTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commandClass;

    /**
     * @ORM\Column(type="text")
     */
    private $commandData;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $runAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $completedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $multithread;

    /**
     * CommandMessage constructor.
     */
    public function __construct()
    {
        $this->setStatus(self::PENDING);
        $this->setCreatedAt(time());
        $this->setMultithread(0);
    }

    public function getCommandClass(): ?string
    {
        return $this->commandClass;
    }

    public function setCommandClass(string $commandClass): self
    {
        $this->commandClass = $commandClass;

        return $this;
    }

    public function getCommandData(): ?string
    {
        return $this->commandData;
    }

    public function setCommandData(string $commandData): self
    {
        $this->commandData = $commandData;

        return $this;
    }

    public function getRunAt(): ?int
    {
        return $this->runAt;
    }

    public function setRunAt(?int $runAt): self
    {
        $this->runAt = $runAt;

        return $this;
    }

    public function getCompletedAt(): ?int
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?int $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getMultithread(): ?int
    {
        return $this->multithread;
    }

    public function setMultithread(int $multithread): self
    {
        $this->multithread = $multithread;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultithread() {
        return $this->multithread > 0;
    }

    public function setError() {
        $this->status = self::ERROR;
        $this->setCompletedAt(time());
    }

    public function setProcessing() {
        $this->status = self::PROCESSING;
        $this->setRunAt(time());
    }

    public function setCompleted() {
        $this->status = self::COMPLETED;
        $this->setCompletedAt(time());
    }
}
