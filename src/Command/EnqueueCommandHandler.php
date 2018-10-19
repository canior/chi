<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 08:49
 */

namespace App\Command;

use App\Entity\CommandMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\ORMException;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class EnqueueCommandHandler //extends AbstractCommandHandler
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
     * EnqueueCommandHandler constructor.
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
     * @param CommandInterface|EnqueueCommand $command
     * @return mixed
     * @throws ORMException
     */
    public function handle(EnqueueCommand $command)
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
        $this->em->persist($commandMessage);
        $this->em->flush();

        $this->log->info('queued message id : ' . $commandMessage->getId() . ', multithread: ' . $command->isMultithread());

        return $commandMessage->getId();
    }
}