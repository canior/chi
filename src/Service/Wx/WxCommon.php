<?php
/**
 * User: YuYechao
 * Date: 2017-09-08
 */

namespace App\Service\Wx;

use App\Command\File\UploadFileCommand;
use App\Entity\File as FileDao;
use App\Repository\FileRepository;
use App\Service\Wx\WxTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * WxCommon
 * 微信小程序常用接口库
 */
class WxCommon
{
    const API_URL = 'https://api.weixin.qq.com';

    use WxTrait;

    private $appId;
    private $appSecret;
    private $appKey;
    private $mchId;
    private $wxNotifyUrl;
    private $log;

    /**
     * WxCommon constructor.
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        $this->appId = 'wx51a92106ac38c489';
        $this->appSecret = '5fa2532707515a357a7cf9421b360326';
        $this->log = $log;
    }

    /**
     * 通过code获取session
     * https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html#wxloginobject
     * @param
     * @return array
     */
    public function getSessionByCode($code = '')
    {
        if (!$code) {
            return [
                'status' => false,
                'msg' => 'code_empty',
            ];
        }

        $client = new Client(['base_uri' => self::API_URL]);
        $info = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $paramStr = $this->formatBizQueryParaMap($info);
        $url = "sns/jscode2session?".$paramStr;
        $response = $client->get($url);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        $this->log->info("response:" . $body);

        if($code === 200) {
            $result = json_decode($body, true);
            if (isset($result['errcode'])) {
                return [
                    'status' => false,
                    'msg' => $result['errmsg'],
                    'code' => $result['errcode'],
                ];
            } else {
                return [
                    'status' => true,
                    'data' => $result,
                ];
            }
        } else {
            return [
                'status' => false,
                'msg' => $code
            ];
        }
    }

    /**
     * 获取access_token
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140183
     * @return string $accessToken | false
     */
    public function getAccessToken()
    {
        $client = new Client(['base_uri' => self::API_URL]);
        $url = "cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
        $response = $client->get($url);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        if($code === 200) {
            $result = json_decode($body, true);
            return $result['access_token'];
        } else {
            return false; //TODO 这里如果出问题了怎么办
        }
    }

    /**
     * 获取二维码
     * @param array $params 二维码包含的数据参数
     * @return bool|string
     */
    public function getQrcode($accessToken, $params)
    {
        if (!$accessToken) {
            return false;
        }

        $client = new Client(['base_uri' => self::API_URL]);
        $postInfo = [
            'scene' => urlencode( isset($params['scene']) ? $params['scene'] : '' ),
            'page' => isset($params['page']) ? $params['page'] : 'pages/index/index',
            'width' => isset($params['width']) ? $params['width'] : 430,
            'auto_color' => isset($params['auto_color']) ? $params['auto_color'] : true,
            'line_color' => isset($params['line_color']) ? $params['line_color'] : ['r'=>0, 'g'=>0, 'b'=>0],
        ];
        $url = "wxa/getwxacodeunlimit?access_token={$accessToken}";
        $postStr = json_encode($postInfo);
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200
        $body = $response->getBody()->getContents();

        if($code === 200) {
            return $body;
        } else {
            return false;
        }
    }

    /**
     * 解密用户敏感数据
     * @param   $sessionKey
     * @param array $userInfo ($code, encryptedData, iv)
     * @return int
     */
    public function getDecryptData($sessionKey, $userInfo)
    {
        $result = $this->decryptData($sessionKey, $userInfo['encryptedData'], $userInfo['iv']);
        return $result;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $sessionKey
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @return int 成功0，失败返回对应的错误码
     */
    private function decryptData($sessionKey, $encryptedData, $iv)
    {
        if (strlen($sessionKey) != 24) {
            return ['err'=>'sessionKey_len_error'];
        }
        $aesKey = base64_decode($sessionKey);

        if (strlen($iv) != 24) {
            return ['err'=>'iv_len_error'];
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $data = json_decode($result, true);
        if( $data  == NULL )
        {
            return ['err'=>'illegal_buffer'];
        }
        if( $data['watermark']['appid'] != $this->appId )
        {
            return ['err'=>'illegal_buffer_appid'];
        }
        return $data;
    }


    /**
     * https://developers.weixin.qq.com/miniprogram/dev/api/open-api/template-message/sendTemplateMessage.html
     * 发送消息通知接口
     * 用户完成一次支付，小程序可以获得 3 次发送模板消息的机会。
     * 用户提交一次表单，小程序可以获得 1 次发送模板消息的机会。
     *
     * @param string $openId
     * @param string $templateId
     * @param string $page
     * @param string $formId
     * @param array $keywordsMapping ex. ['keywords1' => ['value' => 'abc']]
     * @param string $emphasisKeyword
     * @return string
     */
    public function sendMessage(string $openId, string $templateId, string $page, string $formId, array $keywordsMapping = [], string $emphasisKeyword = null) : string {
        $accessToken = $this->getAccessToken();
        $this->log->info("got access token" . $accessToken);
        $client = new Client(['base_uri' => self::API_URL]);
        $postInfo = [
            'touser' => $openId,
            'template_id' => $templateId,
            'page' => $page,
            'form_id' => $formId,
            'data' => $keywordsMapping,
            'emphasis_keyword' => $emphasisKeyword
        ];
        $url = "/cgi-bin/message/wxopen/template/send?access_token={$accessToken}";
        $postStr = json_encode($postInfo);
        $this->log->info("send message: " . $postStr);
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody()->getContents();
        $this->log->info("send message received: " . $body);
        if($code === 200) {
            return $body;
        } else {
            return "sending message error";
        }
    }

    /**
     * https://developers.weixin.qq.com/miniprogram/dev/api/getWXACodeUnlimit.html
     *
     * @param ObjectManager $entityManager
     * @param string $scene
     * @param $page
     * @param bool $isHyaline
     * @return FileDao|null
     */
    public function createWxQRFile(ObjectManager $entityManager, string $scene, $page, $isHyaline = false) {
        //$page = ""; //TODO 正式时候去掉

        $accessToken = $this->getAccessToken();
        $this->log->info("got access token" . $accessToken);
        $client = new Client(['base_uri' => self::API_URL]);
        $postInfo = [
            'scene' => $scene,
            'page' => $page,
            'is_hyaline' => $isHyaline
        ];
        $url = "/wxa/getwxacodeunlimit?access_token={$accessToken}";
        $postStr = json_encode($postInfo);
        $this->log->info("create QR Code: " . $postStr);
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200

        if($code === 200) {
            $file = $response->getBody();
            $fileName = uniqid() . ".jpeg";
            $md5 = md5($fileName);
            $filePath = 'upload/' . FileDao::createPathFromMD5($md5);

            $absoluteFilePath = __DIR__ . "/../../../public/" . $filePath;

            $this->log->info("create file " . $absoluteFilePath);

            if (!file_exists($absoluteFilePath)) {
                mkdir($absoluteFilePath, 0777, true);
            }

            file_put_contents($absoluteFilePath . $md5 . '.jpeg', $file);

            $fileDao = new FileDao();
            $fileDao->setUploadUser(null)
                ->setName($fileName)
                ->setType('jpeg')
                ->setSize($file->getSize())
                ->setPath($filePath)
                ->setMd5($md5)
                ->setUploadAt(time());
            try {
                $entityManager->persist($fileDao);
                $entityManager->flush();
            } catch (ORMException $e) {
                return null;
            }

            return $fileDao;

        } else {
            return null;
        }
    }
}