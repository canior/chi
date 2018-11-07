<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-11-07
 * Time: 16:51
 */

namespace App\Command\Notification;

use App\Command\CommandInterface;
use App\Entity\GroupOrder;
use App\Repository\GroupOrderRepository;
use App\Service\Wx\WxCommon;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class NotifyExpiredGroupOrderCommandHandler
{
    /**
     * @var ObjectManager $entityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * NotifyExpiredGroupOrderCommandHandler constructor.
     * @param ObjectManager $entityManager
     * @param LoggerInterface $log
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $log)
    {
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    /**
     * 通知团长拼团过期通知, 并退款
     * 商品名称 {{keyword1.DATA}}
     * 订单金额 {{keyword2.DATA}}
     * 过期时间 {{keyword3.DATA}}
     * 温馨提示 {{keyword4.DATA}}
     * 退款金额 {{keyword5.DATA}}
     * 退款方式 {{keyword6.DATA}}
     * @param CommandInterface|NotifyExpiredGroupOrderCommand $command
     * @return mixed
     */
    public function handle(NotifyExpiredGroupOrderCommand $command)
    {
        $groupOrderId = $command->getGroupOrderId();
        /**
         * @var GroupOrderRepository $groupOrderRepository
         */
        $groupOrderRepository = $this->entityManager->getRepository(GroupOrder::class);
        $groupOrder = $groupOrderRepository->find($groupOrderId);
        $groupUserOrder = $groupOrder->getMasterGroupUserOrder();

        $formId = $groupUserOrder->getPrePayId();
        $templateId = "e3Up2LoLjskeL1gHIp3EQhR2KhIjKUVgYPh5hriBJME";
        $page = "pages/group/index?id=" . $groupOrderId;
        $toUser = $groupUserOrder->getUser()->getWxOpenId();
        $data = [['keyword1' => ['value' => $groupOrder->getProduct()->getTitle()]],
            ['keyword2' => ['value' => $groupUserOrder->getTotal()]],
            ['keyword3' => ['value' => $groupOrder->getExpiredAt()]],
            ['keyword4' => ['value' => '您发起的拼团已过期!']],
            ['keyword5' => ['value' => $groupUserOrder->getTotal()]],
            ['keyword6' => ['value' => '退回微信零钱']]];
        $emphasisKeyword = "keyword4.DATA";

        $wxApi = new WxCommon($this->log);
        $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);

        // TODO: 退款
    }
}