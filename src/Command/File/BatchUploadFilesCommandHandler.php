<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 20:48
 */

namespace App\Command\File;

use App\Command\AbstractCommandHandler;
use App\Command\CommandInterface;
use App\DataAccess\DataAccess;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BatchUploadFilesCommandHandler extends AbstractCommandHandler
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
     * @var string
     */
    private $fileUploadPath;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * BatchUploadFilesCommandHandler constructor.
     * @param CommandBus $commandBus
     * @param DataAccess $dataAccess
     * @param ContainerInterface $container
     * @param LoggerInterface $log
     */
    public function __construct(CommandBus $commandBus, DataAccess $dataAccess, ContainerInterface $container, LoggerInterface $log)
    {
        $this->commandBus = $commandBus;
        $this->dataAccess = $dataAccess;
        $this->fileUploadPath = $container->getParameter('file_upload_path');
        $this->log = $log;
    }

    /**
     * @param CommandInterface|BatchUploadFilesCommand $command
     * @return mixed
     */
    public function handle(CommandInterface $command)
    {
        $this->log->info('start processing files');

        $fileIds = [];
        foreach($command->getFiles() as $file) {
            /*
             * TODO: 需要搞清楚command chain之间怎么sync
             */
            $uploadFileCommand = new UploadFileCommand($file, $command->getUploadUserId());
            $uploadFileCommandHandler = new UploadFileCommandHandler($this->commandBus, $this->dataAccess, $this->fileUploadPath, $this->log);
            $fileIds[] = $uploadFileCommandHandler->handle($uploadFileCommand);
        }

        $this->log->info('end processing files');
        return $fileIds;
    }
}