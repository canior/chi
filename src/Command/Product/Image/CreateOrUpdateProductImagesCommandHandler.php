<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-18
 * Time: 17:50
 */

namespace App\Command\Product\Image;

use App\Command\AbstractCommandHandler;
use App\Command\CommandInterface;
use App\Entity\File;
use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class CreateOrUpdateProductImagesCommandHandler extends AbstractCommandHandler
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
     * CreateOrUpdateProductImagesCommandHandler constructor.
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
     * @param CommandInterface|CreateOrUpdateProductImagesCommand $command
     * @return mixed|void
     */
    public function handle(CommandInterface $command)
    {
        $this->log->info('start processing product images');

        /**
         * @var Product $product
         */
        $product = $this->em->getRepository(Product::class)->find($command->getProductId());

        if (empty($command->getImages())) {
            $product->getProductImages()->clear();
        } else {
            $images = new ArrayCollection();
            foreach ($command->getImages() as $formImage) {
                if (isset($formImage['id']) && $formImage['id'] > 0) {
                    /**
                     * @var ProductImage $image
                     */
                    $image = $this->em->getRepository(ProductImage::class)->find($formImage['id']);
                } else {
                    $image = new ProductImage();
                }

                $image->setProduct($product)
                    ->setPriority($formImage['priority']);

                /**
                 * @var File $file
                 */
                $file = $this->em->getRepository(File::class)->find($formImage['fileId']);
                $image->setFile($file);

                if (!$product->getProductImages()->contains($image)) {
//                    $product->getProductImages()->add($image);
                    $product->addProductImage($image);
                }
                $images->add($image);
            }
            foreach ($product->getProductImages() as $image) {
                if (!$images->contains($image)) {
//                    $product->getProductImages()->removeElement($image);
                    $product->removeProductImage($image);
                }
            }
        }

        $this->em->persist($product);
        $this->em->flush();

        $this->log->info('end processing product images');
    }
}