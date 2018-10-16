<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 20:48
 */

namespace App\Command\File;

use App\Command\CommandInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BatchUploadFilesCommand implements CommandInterface
{
    /**
     * @var UploadedFile[]
     */
    private $files;

    /**
     * @var int
     */
    private $uploadUserId;

    /**
     * UploadFileCommand constructor.
     * @param UploadedFile[] $files
     * @param int $uploadUserId
     */
    public function __construct(array $files = [], $uploadUserId = null)
    {
        $this->files = $files;
        $this->uploadUserId = $uploadUserId;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile[] $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return int
     */
    public function getUploadUserId()
    {
        return $this->uploadUserId;
    }

    /**
     * @param int $uploadUserId
     */
    public function setUploadUserId($uploadUserId)
    {
        $this->uploadUserId = $uploadUserId;
    }
}