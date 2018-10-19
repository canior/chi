<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-27
 * Time: 18:30
 */

namespace App\Entity\Traits;

trait IdTrait
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
}