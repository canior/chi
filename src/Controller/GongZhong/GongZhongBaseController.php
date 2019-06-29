<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/27
 * Time: 13:34
 */

namespace App\Controller\GongZhong;


use App\Controller\AppApi\AppApiBaseController;
use App\DataAccess\DataAccess;
use App\Service\Config\DependencyInjectionSingletonConfig;
use Endroid\QrCode\Factory\QrCodeFactory;
use League\Tactician\CommandBus;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GongZhongBaseController extends AppApiBaseController
{
    /**
     * @var QrCodeFactory $qrCodeFactory
     */
    protected $qrCodeFactory;
    public function __construct(LoggerInterface $logger, CommandBus $commandBus, DataAccess $dataAccess, JWTTokenManagerInterface $jwtTokenManage, RequestStack $requestStack, ContainerInterface $container, QrCodeFactory $qrCodeFactory)
    {
        parent::__construct($logger, $commandBus, $dataAccess, $jwtTokenManage, $requestStack, $container);
        DependencyInjectionSingletonConfig::getInstance()->setQrCodeFactory($qrCodeFactory);
    }
}