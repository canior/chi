<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-11-01
 * Time: 21:54
 */

namespace App\Command\Product;

use App\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateProductRewardsCommand implements CommandInterface
{
    /**
     * @var int
     * @Assert\NotBlank(message="产品ID不能为空")
     */
    private $productId;

    /**
     * UpdateProductRewardsCommand constructor.
     * @param int $productId
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Get productId
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }
}