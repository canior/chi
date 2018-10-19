<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-19
 * Time: 15:40
 */

namespace App\Command;

use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class EnqueueTestCommandHandler
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
     * MonitorOrderCommandHandler constructor.
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
     * @param CommandInterface|EnqueueTestCommand $command
     * @return mixed
     */
    public function handle(EnqueueTestCommand $command)
    {
        $this->log->info('start testing enqueue command handler');
        dump('enqueue command handler testing success');
        $this->log->info('end testing enqueue command handler');
    }
}