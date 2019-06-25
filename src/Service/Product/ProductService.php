<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/25
 * Time: 17:29
 */

namespace App\Service\Product;

use App\Entity\Product;
use App\Service\Util\FactoryUtil;

/**
 * Class Product
 * @package App\Service\Product
 * @author zxqc2018
 */
class ProductService
{
    /**
     * @param int $productId
     * @return Product
     * @author zxqc2018
     */
    public function getProductById(int $productId)
    {
        /**
         * @var Product $product
         */
        $product = FactoryUtil::productRepository()->find($productId);

        return $product;
    }
}