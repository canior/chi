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
use App\DataAccess\DataAccess;
use App\Entity\Category;
use App\Entity\ProjectVideoMeta;
use App\Entity\User;
use App\Repository\ProjectVideoMetaRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use League\Tactician\CommandBus;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AppApiBaseController extends BaseController
{
    private $jwtTokenManage;

    private $appRequest;

    /**
     * AppApiBaseController constructor.
     * @param LoggerInterface $logger
     * @param CommandBus $commandBus
     * @param DataAccess $dataAccess
     * @param JWTTokenManagerInterface $jwtTokenManage
     * @param RequestStack $requestStack
     */
    public function __construct(LoggerInterface $logger, CommandBus $commandBus, DataAccess $dataAccess, JWTTokenManagerInterface $jwtTokenManage, RequestStack $requestStack)
    {
        parent::__construct($logger, $commandBus, $dataAccess);
        $this->jwtTokenManage = $jwtTokenManage;
        $this->appRequest = $requestStack->getCurrentRequest();
    }
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
     * 获取用户ID只要头有传token就解析[用于非登陆api中获取登陆用户信息]
     * @return int
     * @author zxqc2018
     */
    public function getAppUserId()
    {
        $tokenInfo = [];
        try {
            $authorizationHeaderTokenExtractor = new AuthorizationHeaderTokenExtractor('Bearer', 'authorization');

            $rawToken = $authorizationHeaderTokenExtractor->extract($this->appRequest);
            if (!empty($rawToken)) {
                $token = new JWTUserToken();
                $token->setRawToken($rawToken);
                $tokenInfo = $this->jwtTokenManage->decode($token);
            }
        } catch (\Throwable $e) {

        }

        return $tokenInfo['userId'] ?? 0;
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

        //默认参数定义
        $isDefaultConvert = $extensionParam['isDefaultConvert'] ?? true;
        $paramKeysDefault = $extensionParam['paramKeysDefault'] ?? [];
        $paginatorAutoProcess = $extensionParam['paginatorAutoProcess'] ?? true;
        $requestDataFrom = $extensionParam['requestDataFrom'] ?? 'ALL';

        switch (strtoupper($requestDataFrom)) {
            case 'GET':
                $data = $request->query->all();
                break;
            case 'POST':
                $data = $request->request->all();
                break;
            case 'POST_JSON':
                $data = CommonUtil::mixedTwoWayOpt($request->getContent());
                break;
            default:
                $data = array_merge($request->query->all(), $request->request->all(), CommonUtil::mixedTwoWayOpt($request->getContent()));
        }

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

    /**
     * 获取类别视频数组
     * @param Category $category
     * @param User|null $user
     * @return array
     * @author zxqc2018
     */
    public function getCategoryVideoArray(Category $category, ?User $user = null)
    {
        $res = $category->getComplexArray($user);
        $res['aliyunVideoUrl'] = '';
        $res['aliyunVideoImageUrl'] = '';

        $refreshStatus = $category->refreshAliyunVideo();

        if ($refreshStatus) {
            $res['aliyunVideoUrl'] = $category->getAliyunVideoUrl();
            $res['aliyunVideoImageUrl'] = $category->getAliyunVideoImageUrl();
            if ($refreshStatus == 2) {
                $this->entityPersist($category);
            }
        }

        return $res;
    }

    /**
     * 获取配置视频信息
     * @param string $type
     * @return array|mixed
     * @author zxqc2018
     */
    public function getProjectVideoMeta($type)
    {
        $res = [];
        /**
         * @var ProjectVideoMetaRepository $projectVideoMetaRepository
         */
        $projectVideoMetaRepository = $this->getEntityManager()->getRepository(ProjectVideoMeta::class);
        $projectVideoMeta = $projectVideoMetaRepository->findOneBy(['metaKey' => $type]);

        if (empty($projectVideoMeta)) {
            return $res;
        }

        //刷新视频地址
        $refreshStatus = $projectVideoMeta->refreshAliyunVideo();

        if ($refreshStatus) {
            $res['aliyunVideoUrl'] = $projectVideoMeta->getAliyunVideoUrl();
            $res['aliyunVideoImageUrl'] = $projectVideoMeta->getAliyunVideoImageUrl();
            if ($refreshStatus == 2) {
                $this->entityPersist($projectVideoMeta);
            }
        }
        return CommonUtil::getInsideValue($projectVideoMeta);
    }
}