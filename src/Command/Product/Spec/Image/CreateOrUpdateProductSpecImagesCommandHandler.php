<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-18
 * Time: 19:22
 */

namespace App\Command\Product\Spec\Image;

use App\Command\AbstractCommandHandler;
use App\Command\CommandInterface;
use App\Entity\File;
use App\Entity\Product;
use App\Entity\ProductSpecImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class CreateOrUpdateProductSpecImagesCommandHandler extends AbstractCommandHandler
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
     * @param CommandInterface|CreateOrUpdateProductSpecImagesCommand $command
     * @return mixed|void
     */
    public function handle(CommandInterface $command)
    {
        $this->log->info('start processing product spec images');

        /**
         * @var Product $product
         */
        $product = $this->em->getRepository(Product::class)->find($command->getProductId());

        if (empty($command->getSpecImages())) {
            $product->getProductSpecImages()->clear();
        } else {
            $images = new ArrayCollection();
            foreach ($command->getSpecImages() as $formImage) {
                if (isset($formImage['id']) && $formImage['id'] > 0) {
                    /**
                     * @var ProductSpecImage $image
                     */
                    $image = $this->em->getRepository(ProductSpecImage::class)->find($formImage['id']);
                } else {
                    $image = new ProductSpecImage();
                }

                $image->setProduct($product)
                    ->setPriority($formImage['priority']);

                /**
                 * @var File $file
                 */
                $file = $this->em->getRepository(File::class)->find($formImage['fileId']);
                $image->setFile($file);

                if (!$product->getProductSpecImages()->contains($image)) {
                    $product->addProductSpecImage($image);
                }
                $images->add($image);
            }
            foreach ($product->getProductSpecImages() as $image) {
                if (!$images->contains($image)) {
                    $product->removeProductSpecImage($image);
                }
            }
        }

        $this->em->persist($product);
        $this->em->flush();

        $this->log->info('end processing product spec images');
    }
}