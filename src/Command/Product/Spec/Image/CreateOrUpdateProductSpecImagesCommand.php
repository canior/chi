<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-18
 * Time: 19:22
 */

namespace App\Command\Product\Spec\Image;

use App\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrUpdateProductSpecImagesCommand implements CommandInterface
{
    /**
     * @var int
     * @Assert\NotBlank(message="产品ID不能为空")
     */
    private $productId;

    /**
     * @var array[]
     */
    private $specImages;

    /**
     * CreateOrUpdateProductSpecImagesCommand constructor.
     * @param int $productId
     * @param array[] $specImages
     */
    public function __construct(int $productId, array $specImages)
    {
        $this->productId = $productId;
        $this->specImages = $specImages;
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

    /**
     * Get specImages
     *
     * @return array[]
     */
    public function getSpecImages()
    {
        return $this->specImages;
    }
}