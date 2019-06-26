<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/4
 * Time: 19:36
 */

namespace App\Service\Util;


use App\Entity\Category;
use App\Entity\Course;
use App\Entity\File;
use App\Entity\GroupUserOrder;
use App\Entity\Message;
use App\Entity\Product;
use App\Entity\ProductReview;
use App\Entity\User;
use App\Entity\UserAccountOrder;
use App\Repository\CategoryRepository;
use App\Repository\CourseRepository;
use App\Repository\FileRepository;
use App\Repository\GroupUserOrderRepository;
use App\Repository\MessageRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductReviewRepository;
use App\Repository\UserAccountOrderRepository;
use App\Repository\UserRepository;
use App\Service\Pay\Contracts\GatewayInterface;
use App\Service\Pay\Contracts\NotifyInterface;
use App\Service\Pay\NotifyProcess;
use App\Service\Pay\Pay;
use App\Service\Product\OfflineCourseService;
use App\Service\Sms\AliSms;

class FactoryUtil
{

    /**
     * 支付宝支付网关获取
     * @param $gateway
     * @return \App\Service\Pay\Contracts\GatewayInterface
     * @author zxqc2018
     */
    public static function aliPayDriver($gateway)
    {
        return Pay::getInstance()->driver(Pay::ALI_PAY_DRIVER)->gateway($gateway);
    }

    /**
     * 微信支付网关获取
     * @param $gateway
     * @return \App\Service\Pay\Contracts\GatewayInterface
     * @author zxqc2018
     */
    public static function wxPayDriver($gateway)
    {
        return Pay::getInstance()->driver(Pay::WX_PAY_DRIVER)->gateway($gateway);
    }

    /**
     * 获取阿里短信服务类
     * @return AliSms
     * @author zxqc2018
     */
    public static function aliSms()
    {
        return new AliSms();
    }

    /**
     * 支付宝异步通知服务类
     * @return NotifyInterface|GatewayInterface
     * @author zxqc2018
     */
    public static function aliPayNotify()
    {
        return Pay::getInstance()->driver(Pay::ALI_PAY_DRIVER)->notify();
    }

    /**
     * 微信异步通知服务类
     * @return NotifyInterface|GatewayInterface
     * @author zxqc2018
     */
    public static function wxPayNotify()
    {
        return Pay::getInstance()->driver(Pay::WX_PAY_DRIVER)->notify();
    }

    /**
     * 根据异步通知数据获取处理对象
     * @param string $notifyRaw 异步通知字符串
     * @return NotifyProcess
     * @author zxqc2018
     */
    public static function notifyProcess($notifyRaw)
    {
        $notifyProcess = new NotifyProcess($notifyRaw);
        return $notifyProcess;
    }

    /**
     * @return CourseRepository
     * @author zxqc2018
     */
    public static function courseRepository()
    {
        /**
         * @var CourseRepository $repository
         */
        $repository =  CommonUtil::getRepository(Course::class);
        return $repository;
    }

    /**
     * @return ProductRepository
     * @author zxqc2018
     */
    public static function productRepository()
    {
        /**
         * @var ProductRepository $repository
         */
        $repository =  CommonUtil::getRepository(Product::class);
        return $repository;
    }

    /**
     * @return CategoryRepository
     * @author zxqc2018
     */
    public static function categoryRepository()
    {
        /**
         * @var CategoryRepository $repository
         */
        $repository =  CommonUtil::getRepository(Category::class);
        return $repository;
    }


    /**
     * @return GroupUserOrderRepository
     * @author zxqc2018
     */
    public static function groupUserOrderRepository()
    {
        /**
         * @var GroupUserOrderRepository $repository
         */
        $repository =  CommonUtil::getRepository(GroupUserOrder::class);
        return $repository;
    }


    /**
     * @return ProductReviewRepository
     * @author zxqc2018
     */
    public static function productReviewRepository()
    {
        /**
         * @var ProductReviewRepository $repository
         */
        $repository =  CommonUtil::getRepository(ProductReview::class);
        return $repository;
    }


    /**
     * @return UserAccountOrderRepository
     * @author zxqc2018
     */
    public static function userAccountOrderRepository()
    {
        /**
         * @var UserAccountOrderRepository $repository
         */
        $repository =  CommonUtil::getRepository(UserAccountOrder::class);
        return $repository;
    }

    /**
     * @return UserRepository
     * @author zxqc2018
     */
    public static function userRepository()
    {
        /**
         * @var UserRepository $repository
         */
        $repository =  CommonUtil::getRepository(User::class);
        return $repository;
    }

    /**
     * @return FileRepository
     * @author zxqc2018
     */
    public static function fileRepository()
    {
        /**
         * @var FileRepository $repository
         */
        $repository =  CommonUtil::getRepository(File::class);
        return $repository;
    }

    /**
     * @return MessageRepository
     * @author zxqc2018
     */
    public static function messageRepository()
    {
        /**
         * @var MessageRepository $repository
         */
        $repository =  CommonUtil::getRepository(Message::class);
        return $repository;
    }

    public static function OfflineCourseService()
    {
        return new OfflineCourseService();
    }
}