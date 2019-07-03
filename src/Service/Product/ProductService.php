<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/25
 * Time: 17:29
 */

namespace App\Service\Product;

use App\Entity\BianxianUserLevel;
use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\ProjectShareMeta;
use App\Entity\ShareSource;
use App\Entity\Subject;
use App\Entity\User;
use App\Service\Config\ConfigParams;
use App\Service\ErrorCode;
use App\Service\ResultData;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;

/**
 * Class Product
 * @package App\Service\Product
 * @author zxqc2018
 */
class ProductService
{
    /**
     * @param int $productId
     * @return Product
     * @author zxqc2018
     */
    public function getProductById(int $productId)
    {
        /**
         * @var Product $product
         */
        $product = FactoryUtil::productRepository()->find($productId);

        return $product;
    }

    /**
     * 获取产品包括课程详情
     * @param ResultData $requestProcess
     * @param User $user
     * @param string $showType 公众号 gzh  还是 APP  app
     * @return ResultData
     * @author zxqc2018
     */
    public function getDetailInfo(ResultData $requestProcess, ?User $user = null, $showType = 'app')
    {
        $productId = $requestProcess['productId'];
        $url = $requestProcess['url'];

        $product = FactoryUtil::productRepository()->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        $groupUserOrder = null;
        $isDiffShow = false;
        //公众号登陆的用户系统课和直通车课程关联课程判断
        if ($showType == 'gzh' && !empty($user) && $product->isCourseProduct() && !$product->getCourse()->isOnline() &&
            in_array($product->getCourse()->getSubject(), [Subject::SYSTEM_1, Subject::SYSTEM_2, Subject::TRADING]) && !empty($product->getCourse()->getRefCourse())) {
            $isDiffShow = true;
            /**
             * @var GroupUserOrder $groupUserOrder
             */
            $groupUserOrder = FactoryUtil::groupUserOrderRepository()->findOneBy(['product' => $product, 'user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
            if (empty($groupUserOrder)) {
                /**
                 * @var GroupUserOrder $groupUserOrder
                 */
                $groupUserOrder = FactoryUtil::groupUserOrderRepository()->findOneBy(['product' => $product->getCourse()->getRefCourse()->getProduct(), 'user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
            }

            //购买订单之后只显示当时购买的课程
            if (empty($groupUserOrder)) {
                $isAdvanceUp = (BianxianUserLevel::$userLevelPriorityArray[$user->getBianxianUserLevel()] ?? 0) >= 3;
                if ($isAdvanceUp) {
                    if (in_array($product->getCourse()->getSubject(), [Subject::TRADING])) {
                        $product = $product->getCourse()->getRefCourse()->getProduct();
                    }
                } else {
                    if (in_array($product->getCourse()->getSubject(), [Subject::SYSTEM_1, Subject::SYSTEM_1])) {
                        $product = $product->getCourse()->getRefCourse()->getProduct();
                    }
                }
            } else {
                $product = $groupUserOrder->getProduct();
            }
        }

        $productArray = $product->isCourseProduct() ? $product->getCourse()->getCourseVideoArray() : $product->getArray();

        $data = [
            'product' => $productArray,
            'textMetaArray' => [],
            'shareSources' => [],
            'user' => CommonUtil::obj2Array($user),
        ];

        //课程加上对应的权限以及
        if ($product->isCourseProduct()) {
            if ($product->getCourse()->isOnline()) {
                $data['product']['isPermission'] = false;
                $data['product']['callStatus'] = '';
                if (!empty($user)) {
                    $data['product']['isPermission'] = $product->getCourse()->isPermission($user);
                    if (empty($data['product']['isPermission'])) {
                        $newGroupOrder = $user->getNewestGroupUserOrder($product, true);
                        $data['product']['callStatus'] = CommonUtil::getInsideValue($newGroupOrder, 'getStatus', '');
                    }
                }
            } else {

                //假如没有获取到
                if (!$isDiffShow) {
                    /**
                     * @var GroupUserOrder $groupUserOrder
                     */
                    $groupUserOrder = FactoryUtil::groupUserOrderRepository()->findOneBy(['product' => $product, 'user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
                }
                $data['groupUserOrder'] = CommonUtil::obj2Array($groupUserOrder);
                if (!empty($user)) {
                    if ($showType == 'gzh') {
                        $shareSourceResult = FactoryUtil::shareSourceProcess()->createShareSource([ShareSource::GZH, ShareSource::GZH_QUAN], ShareSource::PRODUCT, $user, $product, $url);
                        $data['shareSources'] = $shareSourceResult->getData();
                        //添加shareSourceUser
                        if ($requestProcess['shareSourceId']) {
                            FactoryUtil::shareSourceProcess()->addShareSourceUser($requestProcess['shareSourceId'], $user);
                        }
                    }
                }
            }

            $productRateSum = 0;
            if (!$product->getActiveReviews()->isEmpty()) {
                foreach ($product->getActiveReviews() as $review) {
                    $productRateSum += $review->getRate();
                }
            }

            $data['product']['followId'] = 0;
            $data['product']['isFollow'] = false;
            $data['product']['myReview'] = null;

            if (!empty($user)) {
                $data['product']['followId'] = CommonUtil::obj2Id(FactoryUtil::followCourseMetaRepository()->findOneBy(['dataId' => $product->getCourse()->getId(), 'user' => $user]));
                $data['product']['isFollow'] = !empty($data['product']['followId']);
                $data['product']['myReview'] = CommonUtil::obj2Array($product->getMyReview($user));
            }
            $data['product']['rate'] = !empty($productRateSum) ? number_format($productRateSum / $product->getActiveReviews()->count(), 2, '.', '') : 0;
            $data['productReviews'] = CommonUtil::entityArray2DataArray(FactoryUtil::getPaginator()->paginate($product->getActiveReviews(), $requestProcess['page'], $requestProcess['pageNum']));
        }

        $requestProcess->setData($data);
        return $requestProcess;
    }
}