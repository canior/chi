<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 09:03
 */

namespace App\Command;

use App\DataAccess\DataAccess;
use App\Entity\CommandMessage;
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
         * @var DataAccess $access
         */
        $access = $this->getContainer()->get('App\DataAccess\DataAccess');

        /**
         * @var CommandBus $commandBus
         */
        $commandBus = $this->getContainer()->get('tactician.commandbus');

        /**
         * @var LoggerInterface $log
         */
        $log = $this->getContainer()->get('logger');

        $output->writeln("starting message processor ...");
        $log->info('running a console command message processor ....');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        while (true) {
            $commandQueueArray = $access->getNextGroupOfCommandMessages();

            foreach ($commandQueueArray as $commandQueue) {

                try {
                    $commandMessageId = $commandQueue->getId();

                    $log->info("processing message id : " . $commandMessageId);
                    $output->writeln("processing message id : " . $commandMessageId);

                    /**
                     * @var CommandMessage $q
                     */
                    $q = $access->getDao(CommandMessage::class, $commandMessageId);
                    $access->refresh($q);

                    $commandClass = $q->getCommandClass();
                    $json = $q->getCommandData();
                    $command = $serializer->deserialize($json, $commandClass, 'json');

                    $q->setProcessing();
                    $access->persist($q);
                    $access->flush();

                    $commandBus->handle($command);

                    $q->setCompleted();
                    $access->persist($q);
                    $access->flush();

                    $log->info("completed message id : " . $commandMessageId);
                    $output->writeln("completed message id : " . $commandMessageId);

                    $log->info("ready to delete myself " . $commandMessageId);
                    $access->delete($q);
                    $access->flush();

                } catch (\Exception $e) {
                    $log->error('Exception: ' . $e->getTraceAsString());

                    $commandQueue->setError();
                    $access->persist($commandQueue);
                    $access->flush();

                    continue;
                }
            }

            $log->info("waiting message processor for 5 sec ...");
            $output->writeln("waiting message processor for 5 sec ...");
            sleep(5);
        }
    }
}