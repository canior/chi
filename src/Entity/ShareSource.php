<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TypeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Id\UuidGenerator;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShareSourceRepository")
 */
class ShareSource implements Dao
{
    use TypeTrait,
        CreatedAtTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shareSources")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="shareSources")
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $page;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File")
     * @ORM\JoinColumn(nullable=true)
     */
    private $bannerFile;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSourceUser", mappedBy="shareSource", cascade="persist", orphanRemoval=true)
     */
    private $shareSourceUsers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupOrder", inversedBy="shareSources")
     */
    private $groupOrder;

    const REFER = 'refer';
    const QUAN = 'quan';

    public static $types = [
        self::REFER => '小程序转发',
        self::QUAN => '朋友圈图片',
    ];

    public function __construct()
    {
        $this->id = uniqid(rand(10000,99999));
        $this->setCreatedAt();
        $this->shareSourceUsers = new ArrayCollection();
    }

    /**
     * @param $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    /**
     * 如果是raw，则生成shareSourceId
     * @param string|null $page
     * @param bool $raw
     * @return ShareSource
     */
    public function setPage($page, $raw = false): self
    {
        $this->page = $page;

        if ($raw) {
            if (strpos($page, '?') !== false) {
                $this->page .= "&shareSourceId=" . $this->getId();
            } else {
                $this->page .= "?shareSourceId=" . $this->getId();
            }
        }

        return $this;
    }

    public function getBannerFile(): ?File
    {
        return $this->bannerFile;
    }

    public function setBannerFile(?File $bannerFile): self
    {
        $this->bannerFile = $bannerFile;

        return $this;
    }

    /**
     * @return Collection|ShareSourceUser[]
     */
    public function getShareSourceUsers(): Collection
    {
        return $this->shareSourceUsers;
    }

    /**
     * 分享源是否包含此用户
     *
     * @param User $user
     * @return bool
     */
    public function hasShareSourceUser(User $user) {
        foreach ($this->getShareSourceUsers() as $shareSourceUser) {
            if ($shareSourceUser->getUser()->getId() == $user->getId()) {
                return true;
            }
        }
        return false;
    }

    public function addShareSourceUser(ShareSourceUser $shareSourceUser): self
    {
        if (!$this->shareSourceUsers->contains($shareSourceUser)) {
            $this->shareSourceUsers->add($shareSourceUser);
            $shareSourceUser->setShareSource($this);
        }

        return $this;
    }

    public function removeShareSourceUser(ShareSourceUser $shareSourceUser): self
    {
        if ($this->shareSourceUsers->contains($shareSourceUser)) {
            $this->shareSourceUsers->removeElement($shareSourceUser);
            // set the owning side to null (unless already changed)
            if ($shareSourceUser->getShareSource() === $this) {
                $shareSourceUser->setShareSource(null);
            }
        }

        return $this;
    }

    public function getArray() : array {
        return [
            'id' => $this->id,
            'userId' => $this->getUser() ? $this->getUser()->getId() : null,
            'type' => $this->type,
            'title' => $this->title,
            'bannerFileId' => $this->getBannerFile() ? $this->getBannerFile()->getId() : null,
            'page' => $this->getPage(),
        ];
    }

    public function getGroupOrder(): ?GroupOrder
    {
        return $this->groupOrder;
    }

    public function setGroupOrder(?GroupOrder $groupOrder): self
    {
        $this->groupOrder = $groupOrder;

        return $this;
    }
}
