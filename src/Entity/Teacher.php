<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-13
 * Time: 5:46 PM
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\JoinColumn(nullable=true)
     */
    private $teacherAvatarFile;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="teacher", cascade={"persist"})
     */
    private $user;

    /**
     * @var Course[]
     * @ORM\OneToMany(targetEntity="App\Entity\Course", mappedBy="teacher", cascade={"persist"}, fetch="LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $courses;


    public function __construct() {
        $this->courses = new ArrayCollection();
    }

    /**
     * Teacher constructor.
     * @param string $name
     * @return Teacher
     */
    public static function factory($name) {
        $teacher = new Teacher();
        $teacher->setName($name);
        return $teacher;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
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
     * @return null|string
     */
    public function getTitle(): ?string
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
     * @return null|string
     */
    public function getDescription(): ?string
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
     * @return null|File
     */
    public function getTeacherAvatarFile(): ?File
    {
        return $this->teacherAvatarFile;
    }

    /**
     * @param File|null $teacherAvatarFile
     */
    public function setTeacherAvatarFile(File $teacherAvatarFile): void
    {
        $this->teacherAvatarFile = $teacherAvatarFile;
    }
    /**
     * @return null|User
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
     * @return Course[]
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * @param Course[] $courses
     */
    public function setCourses($courses): void
    {
        $this->courses = $courses;
    }

    /**
     * @return array
     */
    public function getArray() {
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'avatarFileId' => $this->getTeacherAvatarFile() ? $this->getTeacherAvatarFile()->getId() : null
        ];
    }
}