<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-18
 * Time: 17:50
 */

namespace App\Command\Product\Image;

use App\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrUpdateProductImagesCommand implements CommandInterface
{
    /**
     * @var int
     * @Assert\NotBlank(message="产品ID不能为空")
     */
    private $productId;

    /**
     * @var array[]
     */
    private $images;

    /**
     * CreateOrUpdateProductImagesCommand constructor.
     * @param int $productId
     * @param array[] $images
     */
    public function __construct(int $productId, array $images)
    {
        $this->productId = $productId;
        $this->images = $images;
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
     * Get images
     *
     * @return array[]
     */
    public function getImages()
    {
        return $this->images;
    }
}