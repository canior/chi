<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 08:49
 */

namespace App\Command;

use App\DataAccess\DataAccess;
use App\Entity\CommandMessage;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EnqueueCommandHandler extends AbstractCommandHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var DataAccess
     */
    private $dataAccess;

    /**
     * @var LoggerInterface
     */
    private $log;


    /**
     * EnqueueCommandHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->commandBus = $container->get('tactician.commandbus');
        $this->dataAccess = $container->get(DataAccess::class);
        $this->log = $container->get('logger');
    }

    /**
     * @param CommandInterface|EnqueueCommand $command
     * @return mixed
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var EnqueueCommand $qCommand
         */
        $qCommand = $command;

        $this->log->info('enqueue command: ' . $qCommand->getCommandClass());

        $commandMessage = new CommandMessage();
        $commandMessage->setMultithread($command->isMultithread() ? 1 : 0);
        $commandMessage->setCommandClass($qCommand->getCommandClass());
        $commandMessage->setCommandData($qCommand->getJsonData());
        $this->dataAccess->persist($commandMessage);
        $this->dataAccess->flush();

        $this->log->info('queued message id : ' . $commandMessage->getId() . ', multithread: ' . $command->isMultithread());

        return $commandMessage->getId();
    }
}