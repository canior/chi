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

class UploadFileCommand implements CommandInterface
{
    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @var int
     */
    private $uploadUserId;

    /**
     * UploadFileCommand constructor.
     * @param UploadedFile $file
     * @param int $uploadUserId
     */
    public function __construct(UploadedFile $file, $uploadUserId = null)
    {
        $this->file = $file;
        $this->uploadUserId = $uploadUserId;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
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