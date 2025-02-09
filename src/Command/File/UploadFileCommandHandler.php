<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 20:48
 */

namespace App\Command\File;

use App\Command\CommandInterface;
use App\Entity\File as FileDao;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;

class UploadFileCommandHandler //extends AbstractCommandHandler
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
     * @var string
     */
    private $fileUploadPath;

    /**
     * UploadFileCommandHandler constructor.
     * @param CommandBus $commandBus
     * @param ObjectManager $em
     * @param LoggerInterface $log
     */
    public function __construct(CommandBus $commandBus, ObjectManager $em, LoggerInterface $log)
    {
        $this->commandBus = $commandBus;
        $this->em = $em;
        $this->fileUploadPath = getenv('FILE_UPLOAD_PATH');
        $this->log = $log;
    }

    /**
     * @param CommandInterface|UploadFileCommand $command
     * @return mixed
     */
    public function handle(UploadFileCommand $command)
    {
        $this->log->info('start processing file');

        $file = $command->getFile();
        $uploadUserId = $command->getUploadUserId();

        $fileMD5 = md5_file($file->getRealPath());

        $filePath = $this->fileUploadPath . $this->createPathFromMD5($fileMD5);
        $file->move($filePath, $fileMD5 . '.' . $file->getClientOriginalExtension());

        /**
         * @var FileDao $fileDao
         */
        $fileDao = $this->em->getRepository(FileDao::class)->findOneBy(['md5' => $fileMD5]);
        if ($fileDao == null) {
            $fileDao = new FileDao();
        }

        /**
         * @var User $uploadUser
         */
        $uploadUser = $this->em->getRepository(User::class)->find($uploadUserId);

        $fileDao->setUploadUser($uploadUser)
            ->setName($file->getClientOriginalName())
            ->setType($file->getClientOriginalExtension())
            ->setSize($file->getMaxFilesize()) // TODO: $file->getSize() catch en error
            ->setPath($filePath)
            ->setMd5($fileMD5)
            ->setUploadAt(time());

        $this->em->persist($fileDao);
        $this->em->flush();

        $this->log->info('end processing file with id ' . $fileDao->getId());

        return $fileDao->getId();
    }

    /**
     * @param $md5
     * @return string
     */
    public function createPathFromMD5($md5) {
        $folder1 = substr($md5, 0, 8);
        $folder2 = substr($md5, 8, 8);
        $folder3 = substr($md5, 16, 8);
        $folder4 = substr($md5, 24, 8);
        return $folder1 .DIRECTORY_SEPARATOR . $folder2 . DIRECTORY_SEPARATOR
            . $folder3 . DIRECTORY_SEPARATOR .$folder4 . DIRECTORY_SEPARATOR;
    }
}