<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/15
 * Time: 22:06
 */

namespace App\Service\Tool;

use App\Service\Config\ConfigParams;
use App\Service\Util\CommonUtil;

/**
 * http客服端服务类
 * Class HttpClient
 * @package App\Service\Tool
 * @author zxqc2018
 */
class HttpClient
{
    /**
     * 主机
     */
    private $_host;

    /**
     * 端口
     */
    private $_port;

    /**
     * 路径
     */
    private $_path;

    /**
     * 超时
     * @var int
     */
    private $_timeout;

    // ------------------------------------------------------------------------

    /**
     * Constructor
     * @param array $config
     * @access public
     */
    public function __construct(array $config = [])
    {
        if(!empty($config)) {
            foreach ($config as $c_key => $c_val) {
                $method_str = 'set' . ucfirst($c_key);
                if(method_exists($this, $method_str)) {
                    $this->$method_str($c_val);
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Get Host
     *
     */
    public function getHost()
    {
        return $this->_host;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Host
     * @param $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->_host = $host;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Port
     *
     */
    public function getPort()
    {
        return $this->_port;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Port
     * @param $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->_port = $port;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Path
     *
     */
    public function getPath()
    {
        return $this->_path;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Path
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Timeout
     *
     * @access public
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Timeout
     *
     * @param integer
     * @return $this
     * @access public
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * 查询处理方法
     * @param string $queryStr get数据
     * @param string $method 请求方式
     * @param null|array $postFields post数据
     * @param null|string|array $cookieFields cookie数据
     * @param array $httpHeaders 自定义http头数组 如['Accept: application/json', 'Cache-Control: no-cache']
     * @return mixed
     * @author zxqc2018
     */
    public function query($queryStr = '', $method = 'GET', $postFields = null, $cookieFields = null, $httpHeaders = [])
    {
        $options = [];

        //解析host信息
        $hostInfo = parse_url($this->_host);


        //只有一个path情况,吧path改为host
        if (!empty($hostInfo['path']) && count($hostInfo) == 1) {
            $hostInfo['host'] = $hostInfo['path'];
            unset($hostInfo['path']);
        }
        $schema = $hostInfo['scheme'] ?? 'http';
        $port = $this->_port ?? $hostInfo['port'] ?? null;
        $host = $hostInfo['host'] ?? $this->_host;
        $path = $this->_path ?? $hostInfo['path'] ?? '/';

        //特殊情况纠正
        if ($schema == 'https') {
            $port = null;
        }

        if ($schema == 'http' && $port == 443) {
            $port = null;
        }

        $url = rtrim($host, '/');

        if (is_numeric($port)) {
            $url .= ":{$port}";
        }

        //去除叠加多余的斜杠
        $url = "$schema://" . rtrim(preg_replace('#/+#', '/', "{$url}/{$path}"), '/');

        if (!empty($queryStr)) {

            $url .= "?$queryStr";
        }

        if (is_numeric($this->_timeout)) {

            $options[CURLOPT_TIMEOUT] = (int)$this->_timeout;
        }

        $method = strtoupper($method);

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if (!empty($postFields)){
                $options[CURLOPT_POSTFIELDS] = $postFields;
            }
        } else if ($method === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            $options[CURLOPT_POSTFIELDS] = json_encode($postFields);
            $httpHeaders = array_merge($httpHeaders, ['Content-Type: text/plain']);
        }

        //处理cookie数据
        if (isset($cookieFields)) {
            if (is_array($cookieFields)) {
                //是否要自动转义中文
                $cookieAutoEncode = true;
                if (isset($cookieFields['autoEncodeSet'])) {
                    $cookieAutoEncode = booleanValue($cookieFields['autoEncodeSet']);
                    unset($cookieFields['autoEncodeSet']);
                }
                $cookieArr = [];
                foreach ($cookieFields as $key => $value) {
                    if ($cookieAutoEncode) {
                        $cookieArr[] = urlencode($key) . "=" . urlencode($value);
                    } else {
                        $cookieArr[] = $key . "=" . $value;
                    }
                }
                $cookieStr = join('; ', $cookieArr);
            } else {
                $cookieStr = $cookieFields;
            }
            if ($cookieStr != '') {
                $options[CURLOPT_COOKIE] = $cookieStr;
            }
        }


        //设置自定义头
        if (!empty($httpHeaders)) {
            $options[CURLOPT_HTTPHEADER] = $httpHeaders;
        }

        $res = self::sole($url, $options);

        return $res;
    }

    /**
     * 执行curl会话
     * @param string $url url地址
     * @param array $options curl会话参数
     * @return mixed
     * @author zxqc2018
     */
    public static function sole($url, array $options = [])
    {
        $default = [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            CURLOPT_TIMEOUT => 5
        ];

        $options = $options + $default;

        $options[CURLOPT_URL] = $url;

        $ch = curl_init();

        curl_setopt_array($ch , $options);

        $res['txt'] = curl_exec($ch);

        $res['inf'] = curl_getinfo($ch);

        if ($code = curl_errno($ch)) {
            $res['err'] = $code;
            $res['error'] = curl_error($ch);
        }

        //开发环境调试输出debug信息
        if(!empty($_GET['debug'])) {
            $debugSigns = explode(',', $_GET['debug']);
            if (in_array('api_zs', $debugSigns) && CommonUtil::isDebug() ) {
                $outputArr = [
                    'url' => $options[CURLOPT_URL],
                    'post_fields' => $options[CURLOPT_POSTFIELDS] ?? [],
                ];
                if (isset($options[CURLOPT_COOKIE])) {
                    $outputArr['cookie'] = $options[CURLOPT_COOKIE];
                }
                $outputArr['error'] = $res['error'] ?? '';
                $outputArr['time'] = $res['inf']['total_time'];
                $outputArr['txt'] = $res['txt'];
                //输出方式判断
                if (substr(php_sapi_name(), 0, 3) == 'cli') {
                    print_r($outputArr);
                } else {
                    header('Content-Type: text/html; charset=utf-8');
                }
                ConfigParams::getLogger()->info('httpRequest', $outputArr);
            }
        }
        curl_close($ch);

        return $res;
    }
}
