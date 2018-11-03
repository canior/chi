<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-03
 * Time: 5:07 PM
 */

namespace App\Command\Console;


use App\Entity\ProjectRewardsMeta;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectBootstrapConsoleCommand extends ContainerAwareCommand
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $log;

    public function __construct(string $name=null, ObjectManager $entityManager, LoggerInterface $log) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    protected function configure() {
        $this->setName('app:project-bootstrap');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $projectRewardsMetaRepository = $this->entityManager->getRepository(ProjectRewardsMeta::class);

        $projectRewardsMeta = $projectRewardsMetaRepository->findOneBy(['metaKey'  => 'project_rewards']);
        if ($projectRewardsMeta == null) {
            $projectRewardsMeta = new ProjectRewardsMeta('project_rewards');
            $projectRewardsMeta->setRewardsMeta(0.8,0.2,0, 0);
            $this->entityManager->persist($projectRewardsMeta);
            $this->entityManager->flush();
        }
    }

}