<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 20:45
 */

namespace App\Controller;

use App\Command\File\BatchUploadFilesCommand;
use App\Command\File\UploadFileCommand;
use App\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class UploadFileController extends DefaultController
{
    /**
     * @Route("/file/download/{fileId}", name="fileDownload")
     * @param int $fileId
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function downloadAction($fileId)
    {
        /**
         * @var File $file
         */
        $file = $this->getDataAccess()->getDao(File::class, $fileId);
        //if (!$file) {
        //TODO
        //return place holder of image
        //}
        $response = new BinaryFileResponse($file->getAbsolutePath());
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getName());
        return $response;
    }

    /**
     * @Route("/file/auth/download/{fileId}", name="fileAuthDownload")
     * @param int $fileId
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function authDownloadAction($fileId, Request $request)
    {
        if (!$this->getUser()) {
            // cache the referrer page
            $request->getSession()->set('_security.main.target_path', $request->headers->get('referer'));
            return $this->redirectToRoute('fos_user_security_login');
        }
        return $this->downloadAction($fileId);
    }

    /**
     * @Route("/image/preview/{fileId}", name="imagePreview")
     * @param int $fileId
     * @return Response
     * @throws
     */
    public function previewImageAction($fileId = null)
    {
        /**
         * @var File $file
         */
        $file = $this->getDataAccess()->getDao(File::class, $fileId);
        if ($file->isImage()) {
            $response = new BinaryFileResponse($file->getAbsolutePath());
            return $response;
        } else if ($file->isVideo()) {
//            $response = new BinaryFileResponse($file->getAbsolutePath());
//            $response->setAutoEtag();
//            $response->headers->set('Content-Type', 'video/ogg');
//            // cache video for one week, not work for streaming?
//            $response->setSharedMaxAge(604800);

//            $file = $file->getAbsolutePath();
//            $size = filesize($file);
//            header("Content-type: video/mp4");
//            header("Accept-Ranges: bytes");
//            if(isset($_SERVER['HTTP_RANGE'])){
//                header("HTTP/1.1 206 Partial Content");
//                list($name, $range) = explode("=", $_SERVER['HTTP_RANGE']);
//                list($begin, $end) =explode("-", $range);
//                if($end == 0){
//                    $end = $size - 1;
//                }
//            }else {
//                $begin = 0; $end = $size - 1;
//            }
//            header("Content-Length: " . ($end - $begin + 1));
//            header("Content-Disposition: filename=".basename($file));
//            header("Content-Range: bytes ".$begin."-".$end."/".$size);
//            $fp = fopen($file, 'rb');
//            fseek($fp, $begin);
//            while(!feof($fp)) {
//                $p = min(1024, $end - $begin + 1);
//                $begin += $p;
//                echo fread($fp, $p);
//            }
//            fclose($fp);

            $url = "https://outin-a7944acc383b11e9a86700163e1a625e.oss-cn-shanghai.aliyuncs.com/1eb988a32edb433ebfc49fdec49cf984/7de1b77d1de64334af0bc0de92a15e4f-36f439e53c29dae4a99a5b4a441cab15-ld.mp4?Expires=1551031951&OSSAccessKeyId=LTAI8bKSZ6dKjf44&Signature=AB4X%2FdCreC9pt99ihQR634UWSQE%3D";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);

            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header("Content-Description: File Transfer");
            header("Content-Transfer-Encoding: binary");
            Header("Content-type: ". 'video/mp4');
            //Header("Content-Length: ".$fileSize);
            flush();

            $result = curl_exec($curl);
            curl_close($curl);

        }
    }

    /**
     * @Route("/video/preview/{fileId}", name="videoPreview")
     * @param int $fileId
     * @return Response
     * @throws
     */
    public function previewVideoAction($fileId = null)
    {
        /**
         * @var File $file
         */
        $file = $this->getDataAccess()->getDao(File::class, $fileId);
        $response = new BinaryFileResponse($file->getAbsolutePath());
        if (in_array($file->getType(), ['ogg', 'ogv'])) {
            $response->setAutoEtag();
            $response->headers->set('Content-Type', 'video/ogg');
            // cache video for one week, not work for streaming?
            $response->setSharedMaxAge(604800);
        }
        return $response;
    }

    /**
     * @Route("/file/upload", name="fileUpload")
     * @param Request $request
     * @return Response
     */
    public function uploadFileAction(Request $request)
    {
        if (!$request->isMethod('POST')) {
            exit;
        }

        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files;
        $fileId = null;
        $name = null;
        foreach ($files as $file) {
            try {
                $command = new UploadFileCommand($file, $this->getUser()->getId());
                $fileId = $this->getCommandBus()->handle($command);
                $name = $file->getClientOriginalName();
            } catch (\Exception $e) {
                $this->getLog()->error('upload file failed {error}', ['error' => $e->getMessage()]);
                return new JsonResponse(json_encode(['status' => false, 'error' => $e->getMessage()]));
            }
        }

        return new JsonResponse(json_encode(['status' => true, 'fileId' => $fileId, 'name' => $name]));
    }

    /**
     * @Route("/file/batchUpload", name="fileBatchUpload")
     * @param Request $request
     * @return Response
     */
    public function batchUploadFileAction(Request $request)
    {
        if (!$request->isMethod('POST')) {
            exit;
        }

        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files;
        $fileIds = [];
        foreach ($files as $file) {
            try {
                $command = new BatchUploadFilesCommand($file, $this->getUser()->getId());
                $ids = $this->getCommandBus()->handle($command);
                $fileIds = array_merge($fileIds, $ids);
            } catch (\Exception $e) {
                $this->getLog()->error('batch upload file failed {error}', ['error' => $e->getMessage()]);
                return new JsonResponse(json_encode(['status' => false]));
            }
        }

        $files = [];
        $details = $this->getDataAccess()->getDaoListBy(File::class, ['id' => $fileIds]);
        foreach ($details as $detail) {
            /**
             * @var File $detail
             */
            $files[] = [
                'id' => $detail->getId(),
                'name' => $detail->getName()
            ];
        }

        return new JsonResponse(['status' => true, 'fileIds' => $fileIds, 'files' => $files]);
    }

    /**
     * @Route("/image/upload", name="imageUpload")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function uploadImageAction(Request $request)
    {
        $imageMimeTypes = [
            image_type_to_mime_type(IMAGETYPE_JPEG),
            image_type_to_mime_type(IMAGETYPE_PNG),
            image_type_to_mime_type(IMAGETYPE_GIF),
            image_type_to_mime_type(IMAGETYPE_BMP)
        ];

        $data = [
            'funcNum' => $request->query->get('CKEditorFuncNum'), // CKEditor提交的重要参数
            'CKEditor' => $request->query->get('CKEditor'), // Optional: instance name (might be used to load a specific configuration file or anything else).
            'langCode' => $request->query->get('langCode'), // Optional: might be used to provide localized messages.
            'url' => null,
            'message' => '' // Usually you will only assign something here if the file could not be uploaded.
        ];

        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files;
        $fileId = null;
        foreach ($files as $file) {
            if (!in_array($file->getMimeType(), $imageMimeTypes)) {
                $data['message'] = '格式不正确，请选择上传图片。';
                break;
            }
            try {
                $command = new UploadFileCommand($file, $this->getUser()->getId());
                $fileId = $this->getCommandBus()->handle($command);
            } catch (\Exception $e) {
                $this->getLog()->error('upload file failed {error}', ['error' => $e->getMessage()]);
                return new JsonResponse(json_encode(['status' => false]));
            }
        }

        if ($fileId) {
//            $data['url'] = $request->getSchemeAndHttpHost() . DIRECTORY_SEPARATOR . $file->getAbsolutePath();
            $data['url'] = $this->generateUrl('imagePreview', ['fileId' => $fileId]);
        } else {
            $data['message'] = '图片上传失败，请稍后尝试。';
        }

        return $this->render('file/imageUpload.html.twig', $data);
    }

    /**
     * @Route("/image/creator", name="imageCreator")
     * @param Request $request
     * @return Response
     */
    public function createImageAction(Request $request)
    {
        $type = $request->query->get('type', 'default');
        $params = array();
        switch ($type) {
            case 'business-logo':
                list($params['background'], $params['color'], $params['width'], $params['height']) = array(
                    'fff', '3eca68', 126, 61
                );
                break;
            case 'product-category':
                list($params['background'], $params['color'], $params['width'], $params['height']) = array(
                    'fff', '3eca68', 168, 155
                );
                break;
            default:
                list($params['background'], $params['color'], $params['width'], $params['height']) = array(
                    'f', '0', 200, 200
                );
                break;
        }
        $background = explode(",", $this->hex2rgb($params['background']));
        $color = explode(",", $this->hex2rgb($params['color']));
        $width = $params['width'];
        $height = $params['height'];

        $name = $request->query->get('name', 'Reserved');
        $desc = $request->query->get('desc', $width . "x" . $height);

        header("Content-Type: image/png");
        $image = @imagecreate($width, $height) or die("Cannot Initialize new GD image stream");

        $background_color = imagecolorallocate($image, $background[0], $background[1], $background[2]);
        $text_color = imagecolorallocate($image, $color[0], $color[1], $color[2]);

        /*$line_color = imagecolorallocate($image, 0xee, 0xee, 0xee);
        for ($i=0; $i<6; $i++) {
            $radius = rand(10, 30);
            imageellipse($image, rand(0, $width), rand(0, $height), $radius, $radius, $line_color);
        }*/
        //imagestring($image, 1, 5, 5, $string, $text_color);
        // TODO: fix path web/bundles/themes/ingreen/fonts/
        putenv('GDFONTPATH=' . $this->get('kernel')->getRootDir() . '/../web/bundles/themes/ingreen/fonts/');
        if ($type == 'business-logo') {
            $box = imagettfbbox(14, 0, 'msyh.ttf', $name); //(0,1)lower left, (4,5)upper right
            $x = intval(($width - ($box[4] - $box[0])) / 2);
            $x = $x < intval($box[0]) ? intval($box[0]) : $x;
            $y = intval(($height - ($box[5] - $box[1])) / 2);
            imagettftext($image, 14, 0, $x, $y, $text_color, 'msyh.ttf', $name);
        } elseif ($type == 'product-category') {
            $box = imagettfbbox(150, 0, 'fontawesome-webfont.ttf', json_decode('"&#xF03E;"'));
            $x = intval(($width - ($box[4] - $box[0])) / 2);
            $y = intval(($height - ($box[5] - $box[1])) / 2);
            $line_color = imagecolorallocate($image, 0xee, 0xee, 0xee);
            imagettftext($image, 150, 0, $x, $y, $line_color, 'fontawesome-webfont.ttf', json_decode('"&#xF03E;"'));
            //$line_color = imagecolorallocate($image, 0xcc, 0xcc, 0xcc);
            //imagerectangle($image, 1, 1, $width-1, $height-1, $text_color);

            $box = imagettfbbox(30, 0, 'msyh.ttf', $name);
            $x = intval(($width - ($box[4] - $box[0])) / 2);
            $x = $x < intval($box[0]) ? intval($box[0]) : $x;
            $y = intval(($height - ($box[5] - $box[1])) / 2);
            imagettftext($image, 30, 0, $x, $y, $text_color, 'msyh.ttf', $name);
        } else {
            $box = imagettfbbox(150, 0, 'fontawesome-webfont.ttf', json_decode('"&#xF03E;"'));
            $x = intval(($width - ($box[4] - $box[0])) / 2);
            $y = intval(($height - ($box[5] - $box[1])) / 2);
            $line_color = imagecolorallocate($image, 0xee, 0xee, 0xee);
            imagettftext($image, 150, 0, $x, $y, $line_color, 'fontawesome-webfont.ttf', json_decode('"&#xF03E;"'));

            $box = imagettfbbox(30, 0, 'msyh.ttf', $name);
            $x = intval(($width - ($box[4] - $box[0])) / 2);
            $x = $x < intval($box[0]) ? intval($box[0]) : $x;
            $y = intval(($height - ($box[5] - $box[1])) / 2);
            imagettftext($image, 30, 0, $x, $y, $text_color, 'msyh.ttf', $name);
        }

        imagepng($image);
        imagedestroy($image);
        exit;
    }

    private function hex2rgb($hex)
    {
        // Copied
        $hex = str_replace("#", "", $hex);

        switch (strlen($hex)) {
            case 1:
                $hex = $hex . $hex;
            case 2:
                $r = hexdec($hex);
                $g = hexdec($hex);
                $b = hexdec($hex);
                break;
            case 3:
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
                break;
            default:
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                break;
        }

        $rgb = array($r, $g, $b);
        return implode(",", $rgb);
    }
}