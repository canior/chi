<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/20
 * Time: 9:07
 */

namespace App\Service\Config;



use Endroid\QrCode\Factory\QrCodeFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 单例保存注入的对象
 * Class DependencyInjectionSingletonConfig
 * @package App\Service\Config
 * @author zxqc2018
 */
class DependencyInjectionSingletonConfig
{
    private static $instance;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QrCodeFactory
     */
    private $qrCodeFactory;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return QrCodeFactory
     */
    public function getQrCodeFactory(): QrCodeFactory
    {
        return $this->qrCodeFactory;
    }

    /**
     * @param QrCodeFactory $qrCodeFactory
     */
    public function setQrCodeFactory(QrCodeFactory $qrCodeFactory): void
    {
        $this->qrCodeFactory = $qrCodeFactory;
    }
}