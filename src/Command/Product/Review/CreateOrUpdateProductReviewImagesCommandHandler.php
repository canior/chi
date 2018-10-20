<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-20
 * Time: 20:36
 */

namespace App\Command\Product\Review;

use App\Entity\File;
use App\Entity\ProductReview;
use App\Entity\ProductReviewImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class CreateOrUpdateProductReviewImagesCommandHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * CreateOrUpdateProductReviewImagesCommandHandler constructor.
     * @param CommandBus $commandBus
     * @param ObjectManager $em
     * @param LoggerInterface $log
     */
    public function __construct(CommandBus $commandBus, ObjectManager $em, LoggerInterface $log)
    {
        $this->commandBus = $commandBus;
        $this->em = $em;
        $this->log = $log;
    }

    /**
     * @param CreateOrUpdateProductReviewImagesCommand $command
     * @return mixed|void
     */
    public function handle(CreateOrUpdateProductReviewImagesCommand $command)
    {
        $this->log->info('start processing product review images');

        /**
         * @var ProductReview $productReview
         */
        $productReview = $this->em->getRepository(ProductReview::class)->find($command->getProductReviewId());

        if (empty($command->getImages())) {
            $productReview->getProductReviewImages()->clear();
        } else {
            $images = new ArrayCollection();
            foreach ($command->getImages() as $formImage) {
                if (isset($formImage['id']) && $formImage['id'] > 0) {
                    /**
                     * @var ProductReviewImage $image
                     */
                    $image = $this->em->getRepository(ProductReviewImage::class)->find($formImage['id']);
                } else {
                    $image = new ProductReviewImage();
                }

                $image->setProductReview($productReview);

                /**
                 * @var File $file
                 */
                $file = $this->em->getRepository(File::class)->find($formImage['fileId']);
                $image->setImageFile($file);

                if (!$productReview->getProductReviewImages()->contains($image)) {
                    $productReview->addProductReviewImage($image);
                }
                $images->add($image);
            }
            foreach ($productReview->getProductReviewImages() as $image) {
                if (!$images->contains($image)) {
                    $productReview->removeProductReviewImage($image);
                }
            }
        }

        $this->em->persist($productReview);
        $this->em->flush();

        $this->log->info('end processing product review images');
    }
}