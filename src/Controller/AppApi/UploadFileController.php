<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/30
 * Time: 13:16
 */

namespace App\Controller\AppApi;


use App\Command\File\UploadFileCommand;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/auth")
 * Class UploadFileController
 * @package App\Controller\AppApi
 * @author zxqc2018
 */
class UploadFileController extends AppApiBaseController
{
    /**
     * app中文件上传
     * @Route("/file/upload", name="appFileUpload", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadFileAction(Request $request)
    {
        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files;
        $fileId = null;
        $name = null;
        $resultData = CommonUtil::resultData();

        if (empty(count($files))) {
            $resultData->setCode(ErrorCode::ERROR_UPLOAD_FILE_NOT_EXISTS);
            return $resultData->toJsonResponse();
        }

        $userId = $this->getUser()->getId();

        //调用上传command
        $runCommandFunc = function (UploadedFile $file) use ($userId){
            $command = new UploadFileCommand($file, $this->getUser()->getId());
            return ['fileId' => $this->getCommandBus()->handle($command), 'name' => $file->getClientOriginalName()];
        };
        $data = [];
        foreach ($files as $fileKey => $file) {

            try {
                if (is_array($file)) {
                    foreach ($file as $fileRowKey => $fileRow) {
                        $data[$fileKey][$fileRowKey] = $runCommandFunc($fileRow);
                    }
                } else {
                    $data[$fileKey] = $runCommandFunc($file);
                }
            } catch (\Exception $e) {
                $this->getLog()->error('upload file failed {error}', ['error' => $e->getMessage()]);
                $resultData['error'] = $e->getMessage();
                $resultData->setCode(ErrorCode::ERROR_UPLOAD_FILE_SAVE);
                return $resultData->toJsonResponse();
            }
        }

        $resultData->setData($data);
        return $resultData->toJsonResponse();
    }
}