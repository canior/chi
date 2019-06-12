<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 11:14
 */

namespace App\Controller\AppApi;


use App\Command\EnqueueCommand;
use App\Command\Sms\SendMsgCommand;
use App\Controller\Api\BaseController;
use App\Entity\User;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AppApiBaseController extends BaseController
{
    /**
     * 获取app登陆用户
     * @return User|object|string|null
     */
    protected function getAppUser()
    {
        $res = null;
        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $this->get('security.token_storage');

        if (empty($tokenStorage->getToken())) {
            return $res;
        }

        if (is_string($tokenStorage->getToken()->getUser()) && $tokenStorage->getToken()->getUser() === 'anon.') {
            return $res;
        }

        return $tokenStorage->getToken()->getUser();
    }

    /**
     * 处理请求
     * @param Request $request 请求对象
     * @param array $paramKeys ["k", "kk"]
     * @param array $keyFilters
     * @param array $extensionParam 扩展参数 下面参数介绍 () 为默认值
     * isDefaultConvert (true) 是否需要默认类型转换 paramKeysDefault ([]) 不能为空的key数组
     * paginatorAutoProcess (true) 是否默认处理分页参数
     * @return \App\Service\ResultData
     * @author zxqc2018
     */
    public function processRequest(Request $request, $paramKeys = [], $keyFilters = [], $extensionParam = [])
    {
        $res = CommonUtil::resultData();

        $data = json_decode($request->getContent(), true);

        $isDefaultConvert = $extensionParam['isDefaultConvert'] ?? true;
        $paramKeysDefault = $extensionParam['paramKeysDefault'] ?? [];
        $paginatorAutoProcess = $extensionParam['paginatorAutoProcess'] ?? true;
        //取得登陆用户

        //默认需要转换int 的 key
        $defaultIntKeyArr = [
            'page', 'pageNum', 'productId', 'groupUserOrderId', 'cateId'
        ];

        foreach ($paramKeys as $key) {
            $res[$key] = $data[$key] ?? $paramKeysDefault[$key] ?? null;
            //自动处理字段的类型转换
            if ($isDefaultConvert) {
                if (in_array($key, $defaultIntKeyArr)) {
                    $res[$key] = intval($res[$key]);
                }
            }

            //字段基本检查 不能为空
            if (in_array($key, $keyFilters) && empty($res[$key])) {
                $res->throwErrorException(ErrorCode::ERROR_PARAM_NOT_ALL_EXISTS, ['errorKey' => $key]);
            }
        }


        //处理分页参数
        if ($paginatorAutoProcess) {
            if (isset($res['page']) && is_numeric($res['page'])) {
                $res['page'] = max(1, $res['page']);
            }

            if (isset($res['pageNum']) && is_numeric($res['pageNum']) && empty($res['pageNum'])) {
                $res['pageNum'] = 20;
            }
        }

        return $res;
    }

    /**
     * 异步发送短信
     * @param string $phone
     * @param array $msgData   如 ['code' => '678599'] 数组字段和模板ID对应
     * @param string $msgTemplateId  SMS_47485035 测试模板ID
     * @author zxqc2018
     */
    public function sendSmsMsg($phone, array $msgData, string $msgTemplateId)
    {
        $command = new SendMsgCommand($phone, $msgData, $msgTemplateId);
        $qCommand = new EnqueueCommand($command);
        try {
            $this->getCommandBus()->handle($qCommand);
        } catch (\Throwable $e) {
            CommonUtil::resultData()->throwErrorException(ErrorCode::ERROR_SMS_SEND_RESPONSE, [], 'can not add SendMsgCommand into EnqueueCommand: ' . $e->getMessage());
        }
    }
}