<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File implements Dao
{
    use IdTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $md5;

    /**
     * @ORM\Column(type="integer")
     */
    private $uploadAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $uploadUser;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->setUploadAt(time());
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $this->getMd5() . '.' . $this->getType();
    }

    public function getMd5(): ?string
    {
        return $this->md5;
    }

    public function setMd5(string $md5): self
    {
        $this->md5 = $md5;

        return $this;
    }

    public function getUploadAt(): ?int
    {
        return $this->uploadAt;
    }

    public function setUploadAt(int $uploadAt): self
    {
        $this->uploadAt = $uploadAt;

        return $this;
    }

    public function getUploadUser(): ?User
    {
        return $this->uploadUser;
    }

    public function setUploadUser(?User $uploadUser): self
    {
        $this->uploadUser = $uploadUser;

        return $this;
    }

    /**
     * @return bool
     */
    public function isImage() {
        return in_array(strtolower($this->getType()), ['png', 'jpeg', 'jpg', 'bmp']);
    }

    /**
     * @return bool
     */
    public function isVideo() {
        return in_array(strtolower($this->getType()), ['mp4', 'ogg', 'ogv']);
    }
}
