<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-11-01
 * Time: 23:38
 */

namespace App\Command\Console;

use App\Entity\ProjectRewardsMeta;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ProjectMetaInitConsoleCommand extends ContainerAwareCommand
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * ProjectMetaInitConsoleCommand constructor.
     */
    public function __construct(ObjectManager $em, LoggerInterface $log)
    {
        parent::__construct();
        $this->em = $em;
        $this->log = $log;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:project-meta-init')

            // the short description shown while running "php bin/console list"
            ->setDescription('Project meta initial')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to init data in project_meta table");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectMetas = Yaml::parseFile(__DIR__ . '/project_meta.yaml');
        foreach ($projectMetas as $class => $projectMeta) {
            $projectMetaRepository = $this->em->getRepository("App:$class");
            if (!$projectMetaRepository->findBy(['metaKey' => $projectMeta['meta_key']])) {
                dump('found new ' . $class, $projectMeta['meta_key'], $projectMeta['meta_value']);
                $class = "App\Entity\\" . $class;
                $obj = new $class($projectMeta['meta_key']);
                if ($obj->isRewardsMeta()) {
                    $obj->setRewardsMeta($projectMeta['meta_value']['captainRewardsRate'], $projectMeta['meta_value']['joinerRewardsRate'], $projectMeta['meta_value']['regularRewardsRate'], $projectMeta['meta_value']['userRewardsRate']);
                }
                $this->em->persist($obj);
            }
        }
        $this->em->flush();
    }
}