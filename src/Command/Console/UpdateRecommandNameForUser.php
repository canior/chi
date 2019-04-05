<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-04-06
 * Time: 1:56 AM
 */

namespace App\Command\Console;

use App\Entity\ProjectShareMeta;
use App\Entity\ShareSource;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\UserRepository;
use App\Entity\User;

class UpdateRecommandNameForUser extends ContainerAwareCommand
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $log;

    public function __construct(ObjectManager $entityManager, LoggerInterface $log, string $name=null) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    protected function configure() {
        $this->setName('app:update-sharer');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->log->info('start update recommand name');

        /**
         * @var UserRepository $userRepository
         */
        $userRepository = $this->entityManager->getRepository(User::class);

        for ($i = 1; $i < 10; $i++) {
            $user = $userRepository->find($i);

            if ($user == null) {
                continue;
            }

            $fromLastShareSource = $user->getLatestFromShareSource();
            if ($fromLastShareSource == null) {
                continue;
            }

            $oldRecommanderName = $user->getRecommanderName();
            $recommanderName = $fromLastShareSource->getUser()->getDisplayName();

            $this->log->info('update user ' . $i . ' from ' . $oldRecommanderName . ' to ' . $recommanderName);
            $user->setRecommanderName($recommanderName);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

    }
}
