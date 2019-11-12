<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/11/10
 * Time: 21:52
 */

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Service\Util\CommonUtil;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\UserUpgradeCodeRepository")
 */
class UserUpgradeCode
{
    use IdTrait,
        CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @var User|null $user
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", name="out_trade_no", length=32)
     */
    private $outTradeNo;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var File|null
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $shareImageFile;

    public function __construct()
    {
        $this->setCreatedAt();
    }

    /**
     * @param string $outTradeNo
     * @param string $type
     * @param null $code
     * @param File $shareImageFile
     * @return UserUpgradeCode
     */
    public static function factory($outTradeNo, $type = BianxianUserLevel::ADVANCED, $code = null, ?File $shareImageFile = null)
    {
        $userUpgradeCode = new self();
        $userUpgradeCode->setOutTradeNo($outTradeNo);
        $userUpgradeCode->setType($type);
        if (is_null($code)) {
            $code = CommonUtil::makeCode();
        }
        $userUpgradeCode->setCode($code);

        if (!is_null($shareImageFile)) {
            $userUpgradeCode->setShareImageFile($shareImageFile);
        }
        return $userUpgradeCode;
    }

    /**
     * @return string
     */
    public function getOutTradeNo(): string
    {
        return $this->outTradeNo;
    }

    /**
     * @param string $outTradeNo
     */
    public function setOutTradeNo(string $outTradeNo): void
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getShareImageFile(): ?File
    {
        return $this->shareImageFile;
    }

    public function setShareImageFile(?File $shareImageFile): self
    {
        $this->shareImageFile = $shareImageFile;

        return $this;
    }

    /**
     * @param User $user
     */
    public function codeUse(User $user)
    {
        $bianxianUpgradeUserOrder = $user->createUpgradeUserOrderByUpgradeCode(UpgradeUserOrder::BIANXIAN, $this->getType());
        if (!empty($bianxianUpgradeUserOrder)) {
            $bianxianUpgradeUserOrder->setApproved(false);
        }
        $this->setUser($user);
        $this->setUpdatedAt();
    }
}