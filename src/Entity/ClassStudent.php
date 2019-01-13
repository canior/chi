<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassStudent
 *
 * @ORM\Table(name="class_student")
 * @ORM\Entity
 */
class ClassStudent
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="class_id", type="integer", nullable=false)
     */
    private $classId;

    /**
     * @var int
     *
     * @ORM\Column(name="student_user_id", type="integer", nullable=false)
     */
    private $studentUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $createdAt;


}
