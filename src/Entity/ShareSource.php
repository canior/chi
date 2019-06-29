<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TypeTrait;
use App\Service\Util\CommonUtil;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"persist"}, inversedBy="shareSources")
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
     * @ORM\OneToMany(targetEntity="App\Entity\ShareSourceUser", mappedBy="shareSource", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $shareSourceUsers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupOrder", inversedBy="shareSources")
     */
    private $groupOrder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contentType;

    // 分享
    const REFER = 'refer';
    const QUAN = 'quan';
    const APP = 'app';
    //公众号分享好友
    const GZH = 'gzh';
    //公众号朋友圈需要生成图片
    const GZH_QUAN = 'gzh_quan';

    // 分享内容的类型
    const GROUP_ORDER = 'group_order';
    const PRODUCT = 'product';
    const USER = 'user';

    // 以下类型为兼容老数据------------------------
    const REFER_GROUP_ORDER = 'referGroupOrder';
    const REFER_PRODUCT = 'referProduct';
    const REFER_USER = 'referUser';

    const QUAN_GROUP_ORDER = 'quanGroupOrder';
    const QUAN_PRODUCT = 'quanProduct';
    const QUAN_USER = 'quanUser';
    // 以上类型为兼容老数据------------------------

    public static $types = [
        self::REFER => '小程序',
        self::QUAN => '朋友圈',
        self::APP => '客户端',
        self::GZH => '公众号',
        self::GZH_QUAN => '公众号-圈',

        // 以下类型为兼容老数据------------------------
        self::REFER_GROUP_ORDER => '小程序拼团分享',
        self::REFER_PRODUCT => '小程序产品分享',
        self::REFER_USER => '小程序用户分享',

        self::QUAN_GROUP_ORDER => '朋友圈拼团分享',
        self::QUAN_PRODUCT => '朋友圈产品分享',
        self::QUAN_USER => '朋友圈用户分享',
        // 以上类型为兼容老数据------------------------
    ];

    public static $contentTypes = [
        self::GROUP_ORDER => '拼团',
        self::PRODUCT => '产品',
        self::USER => '用户',
    ];

    /**
     * @param $shareSourceType
     * @param $page
     * @param User|null $user
     * @param null $shareMetaTitle
     * @param Product|null $product
     * @param File|null $bannerFile
     * @param GroupOrder|null $groupOrder
     * @return ShareSource
     */
    public static function factory($shareSourceType, $page, User $user, File $bannerFile = null, $shareMetaTitle = null, Product $product = null, GroupOrder $groupOrder = null) {

        $shareSource = new ShareSource();
        $shareSource->setType($shareSourceType);
        $shareSource->setUser($user);

        if ($shareSourceType == self::REFER_PRODUCT) {
            $shareSource->setTitle($user->getNickname() . $shareMetaTitle . $product->getTitle());
            if ($product->getShareImageFile()) {
                $shareSource->setBannerFile($product->getShareImageFile());
            }
        }
        else if ($shareSourceType == self::REFER_USER) {
            $shareSource->setTitle($user->getNickname() . ' ' . $shareMetaTitle);
            $shareSource->setBannerFile($bannerFile);
        } else if ($shareSourceType == self::REFER_GROUP_ORDER) {
            $product = $groupOrder->getProduct();
            $shareSource->setTitle($user->getNickname() . $shareMetaTitle . $product->getTitle());
            if ($product->getShareImageFile()) {
                $shareSource->setBannerFile($product->getShareImageFile());
            }
        } else {
            $shareSource->setBannerFile($bannerFile);
        }

        $shareSource->setProduct($product);
        $shareSource->setGroupOrder($groupOrder);
        $shareSource->setPage($page, true);
        return $shareSource;
    }

    /**
     * @param $type
     * @param $contentType
     * @param $page
     * @param User|null $user
     * @param File|null $bannerFile
     * @param null $shareMetaTitle
     * @param Product|null $product
     * @return ShareSource
     */
    public static function factoryNew($type, $contentType, $page, User $user, File $bannerFile = null, $shareMetaTitle = null, Product $product = null) {

        $shareSource = new ShareSource();
        $shareSource->setType($type);
        $shareSource->setContenType($contentType);
        $shareSource->setUser($user);

        if ($type == self::GZH) {
            $shareSource->setTitle($user->getNickname() . $shareMetaTitle . $product->getTitle());
            if ($product->getShareImageFile()) {
                $shareSource->setBannerFile($product->getShareImageFile());
            }
        }else {
            $shareSource->setBannerFile($bannerFile);
        }

        $shareSource->setProduct($product);
        $shareSource->setPage($page);
        return $shareSource;
    }

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

    /**
     * @return string
     * @author zxqc2018
     */
    public function getCombineKey()
    {
        $key = $this->type;
        if (!empty($this->contentType)) {
            $key .= '_' . $this->contentType;
        }
        return $key;
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

    public function getContenType(): ?string
    {
        return $this->contentType;
    }

    public function setContenType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getContenTypeTexts()
    {
        return self::$contentTypes[$this->getContenType()] ?? '';
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function getAppRemark(): ?string
    {
        $remark = null;
        switch ( $this->getType() ) {
            case self::APP:
                switch ( $this->getContenType() ) {
                    case self::GROUP_ORDER:
                        $remark = $this->getGroupOrder()?$this->getGroupOrder()->getId():'';
                        break;
                    case self::PRODUCT:
                        $remark = $this->getProduct()?$this->getProduct()->getTitle():'';
                        break;
                    case self::USER:
                        $remark = $this->getUser()?$this->getUser()->getNickname():'';
                        break;
                    default:
                        $remark = '';
                        break;
                }
                break;
            // 兼容老数据
            case self::QUAN_GROUP_ORDER:
            case self::REFER_GROUP_ORDER:
                $remark = $this->getGroupOrder()?$this->getGroupOrder()->getId():'';
                break;
            case self::QUAN_PRODUCT:
            case self::REFER_PRODUCT:
                $remark = $this->getProduct()?$this->getProduct()->getTitle():'';
                break;
            case self::QUAN_USER:
            case self::REFER_USER:
                $remark = $this->getUser()?$this->getUser()->getNickname():'';
                break;
            default:
                $remark = '';
                break;
        }
        return $remark;
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

    public function getGroupOrder(): ?GroupOrder
    {
        return $this->groupOrder;
    }

    public function setGroupOrder(?GroupOrder $groupOrder): self
    {
        $this->groupOrder = $groupOrder;

        return $this;
    }

    /**
     * @param $user
     * @return ShareSourceUser
     */
    public function createShareSourceUser($user) {
        $shareSourceUser = ShareSourceUser::factory($this, $user);
        $this->addShareSourceUser($shareSourceUser);
        return $shareSourceUser;
    }

    public function getArray() : array {
        return [
            'id' => $this->id,
            'userId' => $this->getUser() ? $this->getUser()->getId() : null,
            'type' => $this->type,
            'typeText' => $this->getTypeText(),
            'contentType' => $this->getContenType(),
            'title' => $this->title,
            'bannerFileId' => $this->getBannerFile() ? $this->getBannerFile()->getId() : null,
            'page' => $this->getPage(),
            'course' => $this->getProduct()? CommonUtil::getInsideValue($this, 'getProduct.getCourse.getArray', []):null,
            'contentTypeText'=>$this->getContenTypeTexts(),
            'remark'=>$this->getAppRemark()
        ];
    }


    public function __toString()
    {
        return $this->getUser() . '分享的' . $this->getTypeText() . ', ssId=' . $this->getId();
    }
}
