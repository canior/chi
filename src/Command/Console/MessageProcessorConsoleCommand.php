<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 09:03
 */

namespace App\Command\Console;

use App\Entity\CommandMessage;
use App\Repository\CommandMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MessageProcessorConsoleCommand extends ContainerAwareCommand
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
     * MessageProcessorConsoleCommand constructor.
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     * @param CommandBus $commandBus
     * @param ObjectManager $em
     * @param LoggerInterface $log
     */
    public function __construct(string $name = null, CommandBus $commandBus, ObjectManager $em, LoggerInterface $log)
    {
        parent::__construct($name);

        $this->commandBus = $commandBus;
        $this->em = $em;
        $this->log = $log;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:message-processor')

            // the short description shown while running "php bin/console list"
            ->setDescription('Processing command messages from queue')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to process message from queue in db");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var CommandMessageRepository $commandMessageRepository
         */
        $commandMessageRepository = $this->em->getRepository(CommandMessage::class);

        $output->writeln("starting message processor ...");
        $this->log->info('running a console command message processor ....');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        while (true) {
            $commandQueueArray = $commandMessageRepository->getNextGroupOfCommandMessages();

            foreach ($commandQueueArray as $commandQueue) {

                try {
                    $commandMessageId = $commandQueue->getId();

                    $this->log->info("processing message id : " . $commandMessageId);
                    $output->writeln("processing message id : " . $commandMessageId);

                    /**
                     * @var CommandMessage $q
                     */
                    $q = $commandMessageRepository->find($commandMessageId);
                    $this->em->refresh($q);

                    $commandClass = $q->getCommandClass();
                    $json = $q->getCommandData();
                    $command = $serializer->deserialize($json, $commandClass, 'json');

                    $q->setProcessing();
                    $this->em->persist($q);
                    $this->em->flush();

                    $this->commandBus->handle($command);

                    $q->setCompleted();
                    $this->em->persist($q);
                    $this->em->flush();

                    $this->log->info("completed message id : " . $commandMessageId);
                    $output->writeln("completed message id : " . $commandMessageId);

                    $this->log->info("ready to delete myself " . $commandMessageId);
                    $this->em->remove($q);
                    $this->em->flush();

                } catch (\Throwable $e) {
                    $this->log->error('Exception Message: ' . $e->getMessage());
                    $this->log->error('Exception: ' . $e->getTraceAsString());

                    $commandQueue->setError();
                    $this->em->persist($commandQueue);
                    $this->em->flush();

                    continue;
                }
            }

            $this->log->info("waiting message processor for 5 sec ...");
            $output->writeln("waiting message processor for 5 sec ...");
            sleep(5);
        }
    }
}