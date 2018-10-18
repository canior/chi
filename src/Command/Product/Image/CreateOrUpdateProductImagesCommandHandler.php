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
use Doctrine\Common\Persistence\ManagerRegistry;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class CreateOrUpdateProductImagesCommandHandler extends AbstractCommandHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * CreateOrUpdateProductImagesCommandHandler constructor.
     * @param CommandBus $commandBus
     * @param ManagerRegistry $doctrine
     * @param LoggerInterface $log
     */
    public function __construct(CommandBus $commandBus, ManagerRegistry $doctrine, LoggerInterface $log)
    {
        $this->commandBus = $commandBus;
        $this->doctrine = $doctrine;
        $this->log = $log;
    }

    /**
     * @param CommandInterface|CreateOrUpdateProductImagesCommand $command
     * @return mixed|void
     */
    public function handle(CommandInterface $command)
    {
        $this->log->info('start processing product images');

        $em = $this->doctrine->getManager();

        /**
         * @var Product $product
         */
        $product = $em->getRepository(Product::class)->find($command->getProductId());

        if (empty($command->getImages())) {
            $product->getProductImages()->clear();
        } else {
            $images = new ArrayCollection();
            foreach ($command->getImages() as $formImage) {
                if (isset($formImage['id']) && $formImage['id'] > 0) {
                    /**
                     * @var ProductImage $image
                     */
                    $image = $em->getRepository(ProductImage::class)->find($formImage['id']);
                } else {
                    $image = new ProductImage();
                }

                $image->setProduct($product)
                    ->setPriority($formImage['priority']);

                /**
                 * @var File $file
                 */
                $file = $em->getRepository(File::class)->find($formImage['fileId']);
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

        $em->persist($product);
        $em->flush();

        $this->log->info('end processing product images');
    }
}