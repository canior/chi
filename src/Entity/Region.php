<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 */
class Region implements Dao
{
    use IdTrait;

    private $full_list = array();
    private $full_name = '';    //省 市 区县(name)
    private $province;          //省dao
    private $city;              //市dao
    private $county;            //区县dao

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Region", mappedBy="parentRegion")
     */
    private $subRegions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="subRegions")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentRegion;

    public function __construct()
    {
        $this->subRegions = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
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

    /**
     * @return Collection|Region[]
     */
    public function getSubRegions(): Collection
    {
        return $this->subRegions;
    }

    public function addSubRegion(Region $subRegion): self
    {
        if (!$this->subRegions->contains($subRegion)) {
            $this->subRegions[] = $subRegion;
            $subRegion->setParentRegion($this);
        }

        return $this;
    }

    public function removeSubRegion(Region $subRegion): self
    {
        if ($this->subRegions->contains($subRegion)) {
            $this->subRegions->removeElement($subRegion);
            // set the owning side to null (unless already changed)
            if ($subRegion->getParentRegion() === $this) {
                $subRegion->setParentRegion(null);
            }
        }

        return $this;
    }

    public function getParentRegion(): ?self
    {
        return $this->parentRegion;
    }

    public function setParentRegion(?self $parentRegion): self
    {
        $this->parentRegion = $parentRegion;

        return $this;
    }

    /**
     * get full_list
     * @return array(id => dao, ...)
     */
    public function getFullList()
    {
        if (count($this->full_list) == 0) {
            $full_list[$this->getId()] = $this;

            $parent = $this->getParentRegion();
            while ($parent) {
                $full_list[$parent->getId()] = $parent;
                $parent = $parent->getParentRegion();
            }
            if (count($full_list) > 1) $full_list = array_reverse($full_list, true);

            $this->full_list = $full_list;
        }

        return $this->full_list;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->full_name == '') {
            $full_list = $this->getFullList();
            foreach ($full_list as $node) {
                $this->full_name = $this->full_name . ' ' . $node->getName();
            }
        }
        return trim($this->full_name);
    }

    /**
     * @return string
     */
    public function getFullNameWithoutProvince()
    {
        $name = '';
        $full_list = $this->getFullList();
        array_shift($full_list);
        foreach ($full_list as $index => $node) {
            $name .= ' ' . $node->getName();
        }
        return trim($name);
    }

    /**
     * @return Region
     */
    public function getProvince()
    {
        if (!$this->province) {
            $full_list = $this->getFullList();
            $daos = array_values($full_list);
            $this->province = $daos[0];
        }
        return $this->province;
    }

    /**
     * @return Region
     */
    public function getCity()
    {
        if (!$this->city) {
            $full_list = $this->getFullList();
            $daos = array_values($full_list);
            if (isset($daos[1])) $this->city = $daos[1];
        }
        return $this->city;
    }

    /**
     * @return Region
     */
    public function getCounty()
    {
        if (!$this->county) {
            $full_list = $this->getFullList();
            $daos = array_values($full_list);
            if (isset($daos[2])) $this->county = $daos[2];
        }
        return $this->county;
    }

    public function getArray() : array {
        return [
            'id' => $this->getId(),
            'fullname' => $this->getFullName(),
            'province' => $this->getProvince()->getName(),
            'city' => $this->getCity()->getName(),
            'county' => $this->getCounty()->getName(),
        ];
    }
}
