<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-04
 * Time: 5:19 PM
 */

namespace App\Command\Payment;

use App\Command\CommandInterface;
use App\Entity\GroupUserOrder;
use App\Repository\GroupUserOrderRepository;
use App\Service\Wx\WxPayment;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;


class RefundOrderCommandHandler
{
    private $entityManager;
    private $log;

    /**
     * @param ObjectManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->log = $logger;
    }

    /**
     * @param CommandInterface|RefundOrderCommand $command
     * @return mixed
     */
    public function handle(RefundOrderCommand $command)
    {
        /**
         * @var GroupUserOrderRepository $groupUserOrderRepository
         */
        $groupUserOrderRepository = $this->entityManager->getRepository(GroupUserOrder::class);
        $groupUserOrder = $groupUserOrderRepository->find($command->getGroupUserOrderId());

        if ($groupUserOrder->isRefunding()) {
            $this->log->info("info wx to send order rewards for id : " . $groupUserOrder->getId());
            $wxPayment = new WxPayment($this->log);
            $result = $wxPayment->refund([
                'outTradeNo' => $groupUserOrder->getId(),
                'outRefundNo' => $groupUserOrder->getId(),
                'totalFee' => $groupUserOrder->getTotal(),
                'refundFee' => $groupUserOrder->getOrderRewards()
            ]);
            $this->log->info("result : " . json_encode($result));
            if ($result['status']) {
                $groupUserOrder->setRefunded();
                $this->entityManager->persist($groupUserOrder);
                $this->entityManager->flush();
            }
        }

    }
}