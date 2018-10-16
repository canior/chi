<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 08:48
 */

namespace App\Command;

class EnqueueCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $commandClazz;

    /**
     * @var string
     */
    private $jsonData;

    /**
     * @var bool
     */
    private $multithread;

    /**
     * EnqueueCommand constructor.
     * @param SerializableCommandInterface $command
     * @param bool $multithread
     */
    public function __construct(SerializableCommandInterface $command, $multithread = false)
    {
        $this->commandClazz = get_class($command);
        $this->jsonData = $command->serialize();
        $this->multithread = $multithread;
    }

    /**
     * Get commandClazz
     *
     * @return string
     */
    public function getCommandClass()
    {
        return $this->commandClazz;
    }

    /**
     * Get jsonData
     *
     * @return string
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * @return bool
     */
    public function isMultithread() {
        return $this->multithread;
    }
}