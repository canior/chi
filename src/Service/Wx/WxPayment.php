<?php
/**
 * User: YuYechao
 * Date: 2017-09-08
 */

namespace App\Service\Wx;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
* WxPayment
*/
class WxPayment
{
    use WxTrait;

    private $appId;
    private $appSecret;
    private $appKey;
    private $mchId;
    private $wxPayApiUrl;
    private $wxPayNotifyUrl;
    private $wxPayRefundNotifyUrl;
    private $certPath;
    private $sslKeyPath;
    private $caInfoPath;
    private $log;

    /**
     * WxPayment constructor.
     * @param $config
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        $this->appId = 'wxc82998253644847f';
        $this->appKey = '22777c0558b563aa6abfa54a12970c26';
        $this->mchId = '1525829301';

        $absoluteFilePath = __DIR__ . "../../../config/bianxian/";
        $this->certPath = $absoluteFilePath . 'apiclient_cert.pem';
        $this->sslKeyPath = $absoluteFilePath . 'apiclient_key.pem';
        $this->caInfoPath = $absoluteFilePath . 'rootca.pem';

        $this->wxPayApiUrl = 'https://api.mch.weixin.qq.com';

        $this->wxPayNotifyUrl = 'https://api.yunlishuju.com/wxapi/notify/order';
        $this->wxPayRefundNotifyUrl = 'https://api.yunlishuju.com/wxapi/notify/order';

        $this->log = $log;
    }

    /**
     * 获取Prepay ID
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1
     * @param $openId
     * @param $outTradeNo
     * @param $amount
     * @param $body
     * @return array
     */
    public function getPrepayId($openId, $outTradeNo, $amount, $body)
    {
        $info = [
            'body' => $body,
            'outTradeNo' => $outTradeNo,
            'totalFee' => $amount,
            'openid' => $openId
        ];

        $appkey = $this->appKey;

        $postData = [
            'appid' => $this->appId,
            'nonce_str' => $this->createNoncestr(),
            'mch_id' => $this->mchId,
            'body' => $info['body'],
            'out_trade_no' => $info['outTradeNo'],
            'total_fee' => $info['totalFee']*100,
            'spbill_create_ip' => $this->getUserIp(),
            'notify_url' => $this->wxPayNotifyUrl,
            'trade_type' => 'JSAPI',
            'openid' => $info['openid'],
        ];
        // 排序待提交数据并签名
        $postData['sign'] = $this->sign($postData);
        $postStr = $this->arrayToXml($postData);
        $this->log->info("sending prepay Id request " . $postStr);

        $client = new Client(['base_uri' => $this->wxPayApiUrl]);
        $url = "pay/unifiedorder";
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        $this->log->info ("wxPayment prepay id response: " . $body);
        if($code === 200) {
            $result = $this->xmlToArray($body);
            // var_dump($result);die();
            if ($result['return_code'] == 'SUCCESS'
                && $result['result_code'] == 'SUCCESS') {

                return [
                    'status' => true,
                    'prepay_id' => $result['prepay_id'],
                ];
            } else {
                return [
                    'status' => false,
                    'return_msg' => $result['return_msg'],
                    'err_code' => isset($result['err_code']) ? $result['err_code'] : null,
                    'err_code_des' => isset($result['err_code_des']) ? $result['err_code_des'] : null,
                ];
            }
        } else {
            return [
                'status' => false,
                'return_msg' => $code
            ];
        }
    }

    /**
     * 退款
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_4
     * @param $info = ['outTradeNo', 'outRefundNo', 'totalFee', 'refundFee']
     * @return array
     */
    public function refund($info)
    {
        $postData = [
            'appid' => $this->appId,
            'nonce_str' => $this->createNoncestr(),
            'mch_id' => $this->mchId,
            'out_trade_no' => $info['outTradeNo'],
            'out_refund_no' => $info['outRefundNo'],
            'total_fee' => $info['totalFee'] * 100,
            'refund_fee' => $info['refundFee'] * 100,
            'op_user_id' => $this->mchId,
            'refund_account' => 'REFUND_SOURCE_UNSETTLED_FUNDS',
            'notify_url' => $this->wxPayRefundNotifyUrl
        ];
        $postData['sign'] = $this->sign($postData);

        $postStr = $this->arrayToXml($postData);
        $this->log->info("sending refund request " . $postStr);

        $client = new Client(['base_uri' => $this->wxPayApiUrl]);
        $url = "secapi/pay/refund";
        $response = $client->post($url, [
            'body' => $postStr,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSLCERTTYPE => 'PEM',
                CURLOPT_SSLCERT => $this->certPath,
                CURLOPT_SSLKEYTYPE => 'PEM',
                CURLOPT_SSLKEY => $this->sslKeyPath,
                CURLOPT_CAINFO => $this->caInfoPath,
            ]
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody(); // body is a stream.

        if($code === 200) {
            $result = $this->xmlToArray($body->getContents());
            return [
                'status' => true,
                'data' => $result
            ];
        } else {
            return [
                'status' => false,
                'msg' => $code
            ];
        }
    }

    public function orderQuery($info)
    {
        $postData = [
            'appid' => $this->appId,
            'nonce_str' => $this->createNoncestr(),
            'mch_id' => $this->mchId,
        ];
        if (isset($info['outTradeNo'])) {
            $postData['out_trade_no'] = $info['outTradeNo'];
        }
        if (isset($info['transactionId'])) {
            $postData['transaction_id'] = $info['transactionId'];
        }
        $postData['sign'] = $this->sign($postData);

        $postStr = $this->arrayToXml($postData);
        $this->log->info("sending order query request " . $postStr);

        $client = new Client(['base_uri' => $this->wxPayApiUrl]);
        $url = "pay/orderquery";
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody(); // body is a stream.

        if($code === 200) {
            $result = $this->xmlToArray($body->getContents());
            if ($result['return_code'] == 'SUCCESS'
                && $result['result_code'] == 'SUCCESS') {
                return [
                    'status' => true,
                    'data' => $result,
                ];
            } else {
                $msg = isset($result['return_msg']) ? $result['return_msg'] : $result['err_code_des'];
                return [
                    'status' => false,
                    'msg' => $msg,
                ];
            }
        } else {
            return [
                'status' => false,
                'msg' => $code,
            ];
        }
    }

    /**
     * 获取下发到小程序的订单数据
     * @param $prepayId
     * @return array
     */
    public function getOrderDataToWxApp($prepayId)
    {
        $signData = [
            'appId' => $this->appId,
            'timeStamp' => (string)time(),
            'nonceStr' => $this->createNoncestr(),
            'package' => 'prepay_id='.$prepayId,
            'signType' => 'MD5',
        ];
        $signData['paySign'] = $this->sign($signData);

        $this->log->info("created sign data " . json_encode($signData));
        return $signData;
    }

    /**
     * 关闭订单
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_3
     * @param $info = [outTradeNo, totalFee, openid]
     */
    public function closeOrder($outTradeNo)
    {
        $appkey = $this->appKey;
        $postData = [
            'appid' => $this->appId,
            'nonce_str' => $this->createNoncestr(),
            'mch_id' => $this->mchId,
            'out_trade_no' => $outTradeNo,
        ];
        // 排序待提交数据并签名
        $postData['sign'] = $this->sign($postData);

        $postStr = $this->arrayToXml($postData);
        $this->log->info("sending order close request " . $postStr);

        $client = new Client(['base_uri' => $this->wxPayApiUrl]);
        $url = "pay/closeorder";
        $response = $client->post($url, [
            'body' => $postStr,
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        if($code === 200) {
            $result = $this->xmlToArray($body);
            // var_dump($result);die();
            if ($result['return_code'] == 'SUCCESS'
                && $result['result_code'] == 'SUCCESS') {
                return [
                    'status' => true,
                    'result_msg' => isset($result['result_msg']) ? $result['result_msg'] : null,
                    'result_code' => isset($result['result_code']) ? $result['result_code'] : null,
                ];
            } else {
                return [
                    'status' => false,
                    'return_msg' => $result['return_msg'],
                    'err_code' => isset($result['err_code']) ? $result['err_code'] : null,
                    'err_code_des' => isset($result['err_code_des']) ? $result['err_code_des'] : null,
                ];
            }
        } else {
            return [
                'status' => false,
                'return_msg' => $code
            ];
        }
    }
}