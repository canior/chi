<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/11
 * Time: 20:18
 */

namespace App\Command\Sms;


use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class SendMsgCommandHandler
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
     * @param SendMsgCommand $command
     * @author zxqc2018
     */
    public function handle(SendMsgCommand $command)
    {
        $phone = $command->getPhone();
        $this->log->info('start send ' . $phone . ' msg');
        FactoryUtil::aliSms()->send($command->getPhone(), $command->getMsgData(), $command->getMsgTemplateId());
        $this->log->info('end send ' . $phone . ' msg');
    }
}