<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-28
 * Time: 07:30
 */

namespace App\Entity\Traits;

trait TypeTrait
{
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeText()
    {
        return isset(self::$types) && isset(self::$types[$this->type]) ? self::$types[$this->type] : $this->type;
    }
}