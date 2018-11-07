<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-11-07
 * Time: 17:28
 */

namespace App\Command\Notification;

use App\Command\CommandInterface;
use App\Entity\GroupUserOrder;
use App\Entity\GroupUserOrderRewards;
use App\Repository\GroupUserOrderRepository;
use App\Service\Wx\WxCommon;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class NotifyUserRewardsSentCommandHandler
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
     * NotifyUserRewardsSentCommandHandler constructor.
     * @param ObjectManager $entityManager
     * @param LoggerInterface $log
     */
    public function __construct(ObjectManager $entityManager, LoggerInterface $log)
    {
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    /**
     * 用户传销返现 收益到账通知
     * 商品名称 {{keyword1.DATA}}
     * 订单号 {{keyword2.DATA}}
     * 收益金额 {{keyword3.DATA}}
     * 收益时间 {{keyword4.DATA}}
     * 收益来源 {{keyword5.DATA}}
     * 温馨提示 {{keyword6.DATA}}
     * @param CommandInterface|NotifyUserRewardsSentCommand $command
     * @return mixed
     */
    public function handle(NotifyUserRewardsSentCommand $command)
    {
        $groupUserOrderId = $command->getGroupUserOrderId();
        /**
         * @var GroupUserOrderRepository $groupUserOrderRepository
         */
        $groupUserOrderRepository = $this->entityManager->getRepository(GroupUserOrder::class);
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        if (!$groupUserOrder->getGroupUserOrderRewards()->isEmpty()) {
            /**
             * @var GroupUserOrderRewards $groupUserOrderReward
             */
            $groupUserOrderReward = $groupUserOrder->getGroupUserOrderRewards()->first();

            $formId = $groupUserOrder->getPrePayId();
            $templateId = "8FIIkmMlRQmluP1qszxpVKiVYhVCXY5qV5LGwJN6jkg";
            $page = "pages/user/reward/index";
            $toUser = $groupUserOrderReward->getUser()->getWxOpenId();
            $data = [
                'keyword1' => ['value' => $groupUserOrder->getProduct()->getTitle()],
                'keyword2' => ['value' => $groupUserOrder->getId()],
                'keyword3' => ['value' => $groupUserOrderReward->getUserRewards()],
                'keyword4' => ['value' => $groupUserOrder->getCompletedAt()],
                'keyword5' => ['value' => '用户拼团返现收益'],
                'keyword6' => ['value' => $groupUserOrder->getUser()->getNickname() . '的拼团返现已发放到微信零钱，请注意查收!']
            ];
            $emphasisKeyword = "";

            $wxApi = new WxCommon($this->log);
            $wxApi->sendMessage($toUser, $templateId, $page, $formId, $data, $emphasisKeyword);
        }
    }
}