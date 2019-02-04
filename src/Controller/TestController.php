<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 20:45
 */

namespace App\Controller;


use App\Repository\GroupUserOrderRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends DefaultController
{
    /**
     * @Route("/test", name="test")
     * @param Request $request
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @return Response
     */
    public function testAction(Request $request, GroupUserOrderRepository $groupUserOrderRepository)
    {
        $groupUserOrderId = 1;
        $groupUserOrder = $groupUserOrderRepository->find($groupUserOrderId);

        $productReview = null;
        $productReviews = $groupUserOrder->getProductReviews();
        if (empty($productReviews)) {
            exit('11111');
            $productReview = new ProductReview();
        } else {
            exit('22222');
            $productReview = $productReviews[0];
        }
        exit;
    }

    /**
     * @Route("/test/img", name="testImg")
     * @param Request $request
     * @return Response
     */
    public function testImgAction(Request $request)
    {
        //背景图
        $bg_file = __DIR__.'/../../wxapp-bianxian/images/demo/shage_bg.png';
        $bg_img = imagecreatefromstring(file_get_contents($bg_file));
        $bg_info = getimagesize($bg_file);
        //二维码
        $qr_file = __DIR__.'/../../wxapp-bianxian/images/demo/share_qr.png';
        $qr_img = imagecreatefromstring(file_get_contents($qr_file));
        $qr_info = getimagesize($qr_file);
        //合并
        imagecopymerge($bg_img, $qr_img, $bg_info[0]-$qr_info[0]-50, $bg_info[1]-$qr_info[1]-100, 0, 0, $qr_info[0], $qr_info[1], 100);
        //写字
        putenv('GDFONTPATH=' . __DIR__.'/../../assets/font/'); //设置中文字体路径
        $font = "msyh.ttf";//字体
        $color = imagecolorallocate($bg_img, 255, 255, 255);
        //imagettftext($bg_img, 20, 0, 50, $bg_info[1]-$qr_info[1]-50, $color, $font, '文字文字文字文字文字');
        //输出到浏览器
        header('content-type:image/png');
        imagepng($bg_img);
        //或输出到文件：$filename
        //imagepng($img, $filename);
        imagedestroy($bg_img);//释放资源
        imagedestroy($qr_img);
        exit;
    }
}