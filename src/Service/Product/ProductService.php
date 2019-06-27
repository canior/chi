<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/25
 * Time: 17:29
 */

namespace App\Service\Product;

use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\User;
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
     * @author zxqc2018
     * @return ResultData
     */
    public function getDetailInfo(ResultData $requestProcess, User $user)
    {
        $productId = $requestProcess['productId'];
        $url = $requestProcess['url'];

        $product = FactoryUtil::productRepository()->find($productId);

        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        //todo 分享


        $productArray = $product->isCourseProduct() ? $product->getCourse()->getCourseVideoArray() : $product->getArray();

        $data = [
            'product' => $productArray,
            'shareSources' => [],
            'textMetaArray' => []
        ];


        //课程加上对应的权限以及
        if ($product->isCourseProduct()) {
            if ($product->getCourse()->isOnline()) {
                $data['product']['isPermission'] = $product->getCourse()->isPermission($user);
                $data['product']['callStatus'] = '';
                if (empty($data['product']['isPermission'])) {
                    $newGroupOrder = $user->getNewestGroupUserOrder($product, true);
                    $data['product']['callStatus'] = CommonUtil::getInsideValue($newGroupOrder, 'getStatus', '');
                }
            } else {
                /**
                 * @var GroupUserOrder $groupUserOrder
                 */
                $groupUserOrder = FactoryUtil::groupUserOrderRepository()->findOneBy(['product' => $product, 'user' => $user, 'paymentStatus' => GroupUserOrder::PAID]);
                $data['groupUserOrder'] = CommonUtil::obj2Array($groupUserOrder);
            }

            $productRateSum = 0;
            if (!$product->getActiveReviews()->isEmpty()) {
                foreach ($product->getActiveReviews() as $review) {
                    $productRateSum += $review->getRate();
                }
            }
            $data['product']['followId'] = CommonUtil::obj2Id(FactoryUtil::followCourseMetaRepository()->findOneBy(['dataId' => $product->getCourse()->getId(), 'user' => $user]));
            $data['product']['isFollow'] = !empty($data['product']['followId']);
            $data['product']['myReview'] = CommonUtil::obj2Array($product->getMyReview($user));
            $data['product']['rate'] = !empty($productRateSum) ? number_format($productRateSum / $product->getActiveReviews()->count(), 2, '.', '') : 0;
            $data['productReviews'] = CommonUtil::entityArray2DataArray(FactoryUtil::getPaginator()->paginate($product->getActiveReviews(), $requestProcess['page'], $requestProcess['pageNum']));
        }

        $requestProcess->setData($data);
        return $requestProcess;
    }
}