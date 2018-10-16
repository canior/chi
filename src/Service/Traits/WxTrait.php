<?php
/**
 * User: yuyechao
 * Date: 2017-09-08
 */

namespace App\Service\Traits;

trait WxTrait
{
    //获取用户IP地址
    static private function getUserIp()
    {
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"]))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else
        {
            $cip = '';
        }
        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }

    // 签名
    public function sign($data)
    {
        $appkey = $this->appKey;
        ksort($data);
        return strtoupper( md5( $this->formatBizQueryParaMap($data)."&key={$appkey}" ) );
    }

    // 验证签名
    public function authSign($data)
    {
        // 提取出返回数据中的sign
        $sign = $data['sign'];
        unset($data['sign']);
        // 对返回数据签名
        $localSign = $this->sign($data);
        // 验证两者签名
        return $sign == $localSign;
    }

    // 数组 转 xml
    static private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">";
             }
             else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    // xml 转 数组
    static private function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    // 创建随机字符串
    static private function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    // 数组 转 http_query
    // 注意: 不能直接用http_query_build, 因为会对中文进行urlencode, 而微信签名不能urlencode
    static private function formatBizQueryParaMap($paraMap, $urlencode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if ($v) {
                if($urlencode)
                {
                   $v = urlencode($v);
                }
                //$buff .= strtolower($k) . "=" . $v . "&";
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    // 接收数据流
    static public function receiveStream()
    {
        $streamData = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        if(empty($streamData)){
            $streamData = file_get_contents('php://input');
        }
        return self::xmlToArray($streamData);
    }

    /**
     * 退款单notify数据解密
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_16&index=9
     */
    public function decryptInfo($reqInfo)
    {
        $appKey = $this->appKey;
        $method = 'AES-256-ECB';
        $methods = openssl_get_cipher_methods();
        if (!in_array($method, $methods)) {
            return [
                'status' => false,
                'msg' => 'no_method'
            ];
        }
        $encrypted = base64_decode($reqInfo);
        $pass = md5($appKey);
        $decrypted = $this->xmlToArray( openssl_decrypt($encrypted, $method, $pass, OPENSSL_RAW_DATA) );
        return [
            'status' => true,
            'data' => $decrypted,
        ];
    }
}