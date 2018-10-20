<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-20
 * Time: 20:36
 */

namespace App\Command\Product\Review;

use App\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrUpdateProductReviewImagesCommand implements CommandInterface
{
    /**
     * @var int
     * @Assert\NotBlank(message="产品评价ID不能为空")
     */
    private $productReviewId;

    /**
     * @var array[]
     */
    private $images;

    /**
     * CreateOrUpdateProductReviewImagesCommand constructor.
     * @param int $productReviewId
     * @param array[] $images
     */
    public function __construct(int $productReviewId, array $images)
    {
        $this->productReviewId = $productReviewId;
        $this->images = $images;
    }

    /**
     * Get productReviewId
     *
     * @return int
     */
    public function getProductReviewId()
    {
        return $this->productReviewId;
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