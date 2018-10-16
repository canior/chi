<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 10:10
 */

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 支付宝小程序常用接口库
 * Class AliCommon
 * @package App\Service
 */
class AliCommon
{
    /**
     * @var \AopClient
     */
    private $aopClient;

    /**
     * @var string
     */
    private $notifyUrl;

    /**
     * AliCommon constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->aopClient = new \AopClient();
        $this->aopClient->appId = $container->getParameter('ali_appid');
        $this->aopClient->rsaPrivateKey = $container->getParameter('ali_private_key');
        $this->aopClient->alipayrsaPublicKey = $container->getParameter('alipay_public_key');
        $this->aopClient->signType = 'RSA2'; // NOTE: We create/upload a RSA2 key, default RSA
        $this->notifyUrl = $container->getParameter('alipay_notify_url');
    }

    /**
     * 获取access_token
     * https://docs.alipay.com/mini/introduce/auth
     *
     * @param string $code
     * @return array|false $accessToken
     */
    public function getAccessToken($code)
    {
        $request = new \AlipaySystemOauthTokenRequest();
        $request->setGrantType('authorization_code');
        $request->setCode($code);
        $result = $this->aopClient->execute($request);
        if ($result && isset($result->alipay_system_oauth_token_response)) {
            return (array)$result->alipay_system_oauth_token_response;
        }
        return false;
    }

    /**
     * 获取二维码
     * @param array $accessToken
     * @param array $params 二维码包含的数据参数
     * @return mixed
     */
    public function getQrcode($accessToken, $params)
    {
        if (!$accessToken) {
            return false;
        }

        $request = new \AlipayOpenAppQrcodeCreateRequest();
        $request->setBizContent("{" .
            "\"url_param\":\"" . (isset($params['page']) ? $params['page'] : 'pages/index/index') . "\"," .
            "\"query_param\":\"x=1\"," .
            "\"describe\":\"" . (isset($params['desc']) ? $params['desc'] : '二维码描述') . "\"" .
            "  }");
        $result = $this->aopClient->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return $result->$responseNode->qr_code_url;
        }
        return false;
    }

    /**
     * 获取 orderStr for my.tradePay
     * https://docs.open.alipay.com/54/106370/
     * https://docs.alipay.com/mini/api/openapi-pay
     * @param $info = [body, subject, outTradeNo, totalAmount]
     * @return mixed
     */
    public function getOrderStr($info)
    {
        // 实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        // SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"" . (isset($info['body']) ? $info['body'] : '') . "\","
            . "\"subject\": \"" . (isset($info['subject']) ? $info['subject'] : '') . "\","
            . "\"out_trade_no\": \"" . $info['outTradeNo'] . "\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"" . $info['totalAmount'] . "\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($bizcontent);
        // 这里和普通的接口调用不同，使用的是sdkExecute
        return $this->aopClient->sdkExecute($request);
    }

    /**
     * 退款
     * https://docs.open.alipay.com/api_1/alipay.trade.refund
     * @param $info = [totalFee, refundFee, outRefundNo, outTradeNo]
     * @return mixed
     */
    public function refund($info)
    {
        $request = new \AlipayTradeRefundRequest();
        // SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"out_trade_no\":\"" . (isset($info['outTradeNo']) ? $info['outTradeNo'] : '') . "\"," // 订单支付时传入的商户订单号,不能和 trade_no同时为空。
            . "\"out_request_no\":\"" . $info['outRefundNo'] . "\"," // 标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
            . "\"refund_amount\":\"" . $info['refundFee'] . "\","
            . "\"refund_reason\":\"退押金\""
            . "}";
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($bizcontent);
        $result = $this->aopClient->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return $result->$responseNode;
    }

    /**
     * 服务端验证异步通知信息参数示例
     * https://docs.open.alipay.com/54/106370/
     * https://docs.open.alipay.com/203/105286
     * @param $params
     * @param $rsaPublicKeyFilePath
     * @param $signType
     * @return mixed
     */
    public function rsaCheckV1($params, $rsaPublicKeyFilePath, $signType = 'RSA2')
    {
        return $this->aopClient->rsaCheckV1($params, $rsaPublicKeyFilePath, $signType);
    }
}