<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 5:46 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdTrait;


/**
 * @ORM\Entity(repositoryClass="App\Repository\TeacherRepository")
 */
class Teacher implements Dao
{
    use IdTrait;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var File
     * @ORM\ManyToOne(targetEntity="App\Entity\File")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teacherAvatarFile;

    /**
     * Teacher constructor.
     * @param string $name
     */
    public function __construct($name) {
        $this->setName($name);
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return File
     */
    public function getTeacherAvatarFile(): File
    {
        return $this->teacherAvatarFile;
    }

    /**
     * @param File $teacherAvatarFile
     */
    public function setTeacherAvatarFile(File $teacherAvatarFile): void
    {
        $this->teacherAvatarFile = $teacherAvatarFile;
    }
}