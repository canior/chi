<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-28
 * Time: 07:31
 */

namespace App\Entity\Traits;

trait DescriptionTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}