<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-11-01
 * Time: 21:54
 */

namespace App\Command\Product;

use App\Command\CommandInterface;
use App\Entity\Product;
use App\Entity\ProjectRewardsMeta;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class UpdateProductRewardsCommandHandler
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
     * UpdateProductRewardsCommandHandler constructor.
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
     * @param CommandInterface|UpdateProductRewardsCommand $command
     * @return mixed|void
     */
    public function handle(UpdateProductRewardsCommand $command)
    {
        $this->log->info('start updating product rewards');

        /**
         * @var Product $product
         */
        $product = $this->em->getRepository(Product::class)->find($command->getProductId());

        $projectRewardsMeta = $this->em->getRepository(ProjectRewardsMeta::class)->findOneBy(['metaKey' => ProjectRewardsMeta::PRODUCT_REWARDS]);
        if ($projectRewardsMeta) {
            $product->setCaptainRewards($product->getRewards() * $projectRewardsMeta->getCaptainRewardsRate());
            $product->setJoinerRewards($product->getRewards() * $projectRewardsMeta->getJoinerRewardsRate());
            $product->setRegularRewards($product->getRewards() * $projectRewardsMeta->getRegularRewardsRate());
            $product->setParentRewards($product->getRewards() * $projectRewardsMeta->getUserRewardsRate());
            $this->em->persist($product);
            $this->em->flush();
        }

        $this->log->info('end updating product rewards');
    }
}