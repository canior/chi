<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/29
 * Time: 16:41
 */

namespace App\Service\Order;

use App\Entity\BianxianUserLevel;
use App\Entity\Subject;
use App\Entity\UpgradeUserOrder;
use App\Entity\User;
use App\Entity\UserLevel;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;

/**
 * Class PartnerAssistantProcess
 * @package App\Service\Order
 * @author zxqc2018
 */
class PartnerAssistantProcess
{

    /**
     * 确认系统学员身份
     * @param User $user
     * @param $orderId
     * @return \App\Service\ResultData
     * @author zxqc2018
     */
    public function partnerConfirmSystemUser(User $user, $orderId)
    {
        $res = CommonUtil::resultData();
        $order = FactoryUtil::groupUserOrderRepository()->find($orderId);


        if (empty($order)) {
            $res->throwErrorException(ErrorCode::ERROR_PAY_ORDER_ID_NO_EXISTS, []);
        }

        $product = $order->getProduct();
        if (!($product->getCourse() && !$product->getCourse()->isOnline() && in_array($product->getCourse()->getSubject(), [Subject::SYSTEM_2, Subject::SYSTEM_1]))) {
            $res->throwErrorException(ErrorCode::ERROR_PARTNER_UP_COURSE_NOT_SYSTEM, []);
        }

        if ($order->getUser()->isBianxianAdvancedUser()) {
            $res->throwErrorException(ErrorCode::ERROR_USER_ALREADY_SYSTEM, []);
        }

        if ($order->getUser()->getParentUser() !== $user) {
            $res->throwErrorException(ErrorCode::ERROR_PARTNER_USER_REF, []);
        }

        if (!$user->isHasRecommendStockLevel()) {
            $res->throwErrorException(ErrorCode::ERROR_NOT_PARTNER_UP_LEVEL, []);
        }

        if ($user->getRecommandStock() <= 0) {
            $res->throwErrorException(ErrorCode::ERROR_PARTNER_RECOMMEND_STOCK_EMPTY, []);
        }

        $jinqiuUpgradeUserOrder = $order->getUser()->createUpgradeUserOrder(UpgradeUserOrder::JINQIU, UserLevel::ADVANCED3, $order);
        if ($jinqiuUpgradeUserOrder) {
            $jinqiuUpgradeUserOrder->setApproved(false);
            $order->addUpgradeUserOrder($jinqiuUpgradeUserOrder);
        }

        $bianxianUpgradeUserOrder = $order->getUser()->createUpgradeUserOrder(UpgradeUserOrder::BIANXIAN, BianxianUserLevel::ADVANCED, $order);
        if ($bianxianUpgradeUserOrder) {
            $bianxianUpgradeUserOrder->setApproved(false);
            $order->addUpgradeUserOrder($bianxianUpgradeUserOrder);
        }

        //TODO 如果合伙人没有名额了怎么办
        $topParentUser = $order->getUser()->getParentUser();
        if (!empty($topParentUser)) {
            $memo = '合伙人推荐系统学员成功，消除名额';
            $topParentUser->createUserRecommandStockOrder(-1, $bianxianUpgradeUserOrder, $memo);
        }

        CommonUtil::entityPersist($order);
        $res['groupUserOrder'] = $order->getArray();

        return $res;
    }
}