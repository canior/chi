<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-19
 * Time: 9:04 PM
 */

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;


/**
 * UserAccountOrder
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRecommandStockOrderRepository")
 */
class UserRecommandStockOrder implements Dao
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userRecommandStockOrders", cascade = {"persist"} )
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="qty", type="integer", nullable=false)
     */
    private $qty;

    /**
     * @var UpgradeUserOrder|null
     * @ORM\ManyToOne(targetEntity="App\Entity\UpgradeUserOrder")
     */
    private $upgradeUserOrder;


    /**
     * @var string|null
     *
     * @ORM\Column(name="memo", type="text", nullable=true)
     */
    private $memo;


    /**
     * @param User $user
     * @param int $qty
     * @param null $upgradeUserOrder
     * @param string $memo
     * @return UserRecommandStockOrder
     */
    public static function factory(User $user, $qty, $upgradeUserOrder = null, $memo = null) {
        $userRecommandStockOrder = new UserRecommandStockOrder();
        $userRecommandStockOrder->setUser($user);
        $userRecommandStockOrder->setQty($qty);
        $userRecommandStockOrder->setUpgradeUserOrder($upgradeUserOrder);
        $userRecommandStockOrder->setMemo($memo);
        $user->increaseRecommandStock($qty);

        return $userRecommandStockOrder;
    }

    public function __construct() {
        $this->setQty(0);
        $this->setCreatedAt();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getQty(): int
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     */
    public function setQty(int $qty): void
    {
        $this->qty = $qty;
    }

    /**
     * @return UpgradeUserOrder|null
     */
    public function getUpgradeUserOrder(): ?UpgradeUserOrder
    {
        return $this->upgradeUserOrder;
    }

    /**
     * @param UpgradeUserOrder|null $upgradeUserOrder
     */
    public function setUpgradeUserOrder(?UpgradeUserOrder $upgradeUserOrder): void
    {
        $this->upgradeUserOrder = $upgradeUserOrder;
    }

    /**
     * @return string|null
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @param string $memo
     */
    public function setMemo(?string $memo): void
    {
        $this->memo = $memo;
    }

    public function getArray() {
        return [
            'id' => $this->getId(),
            'createdAt' => $this->getCreatedAt(),
            'createdAtFormatted' => $this->getCreatedAtFormatted(),
            'memo' => $this->getMemo(),
            'upgradeUserOrder' => $this->getUpgradeUserOrder() ? $this->getUpgradeUserOrder()->getArray() : null,
            'qty' => $this->getQty(),
        ];
    }

}