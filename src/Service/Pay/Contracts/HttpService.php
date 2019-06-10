<?php
namespace App\Service\Pay\Contracts;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * 网络访问工具
 * Class HttpService
 * @package App\Service\Pay\Contracts
 */
class HttpService
{
    /**
     * @var FilesystemAdapter
     */
    private static $cacheInstance;

    /**
     * @return FilesystemAdapter
     * @author zxqc2018
     */
    public static function getCacheInstance()
    {
        if (is_null(static::$cacheInstance)) {
            static::$cacheInstance = new FilesystemAdapter();
        }

        return static::$cacheInstance;
    }

    /**
     * 以get访问模拟访问
     * @param string $url 访问URL
     * @param array $query GET数
     * @param array $options
     * @return bool|string
     */
    public static function get($url, $query = [], $options = [])
    {
        $options['query'] = $query;
        return self::request('get', $url, $options);
    }

    /**
     * 以post访问模拟访问
     * @param string $url 访问URL
     * @param array $data POST数据
     * @param array $options
     * @return bool|string
     */
    public static function post($url, $data = [], $options = [])
    {
        $options['data'] = $data;
        return self::request('post', $url, $options);
    }


    /**
     * CURL模拟网络请求
     * @param string $method 请求方法
     * @param string $url 请求方法
     * @param array $options 请求参数[headers,data,ssl_cer,ssl_key]
     * @return bool|string
     */
    protected static function request($method, $url, $options = [])
    {
        $curl = curl_init();
        // GET参数设置
        if (!empty($options['query'])) {
            $url .= stripos($url, '?') !== false ? '&' : '?' . http_build_query($options['query']);
        }
        // POST数据设置
        if (strtolower($method) === 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, self::build($options['data']));
        }
        // CURL头信息设置
        if (!empty($options['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        }
        // 证书文件设置
        if (!empty($options['ssl_cer'])) {
            if (file_exists($options['ssl_cer'])) {
                curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($curl, CURLOPT_SSLCERT, $options['ssl_cer']);
            } else {
                throw new InvalidArgumentException("Certificate files that do not exist. --- [{$options['ssl_cer']}]");
            }
        }
        if (!empty($options['ssl_key'])) {
            if (file_exists($options['ssl_key'])) {
                curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($curl, CURLOPT_SSLKEY, $options['ssl_key']);
            } else {
                throw new InvalidArgumentException("Certificate files that do not exist. --- [{$options['ssl_key']}]");
            }
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        list($content, $status) = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * POST数据过滤处理
     * @param array $data
     * @return array
     */
    private static function build($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            if (is_string($value) && class_exists('CURLFile', false) && stripos($value, '@') === 0) {
                $filename = realpath(trim($value, '@'));
                if ($filename && file_exists($filename)) {
                    $data[$key] = new \CURLFile($filename);
                }
            }
        }
        return $data;
    }

    /**
     * 缓存配置与存储
     * @param string $name 缓存名称
     * @param string $value 缓存内容
     * @param int $expired 缓存时间(0表示永久缓存)
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public static function setCache($name, $value = '', $expired = 3600)
    {
        $cacheItem = static::getCacheInstance()->getItem($name);
        $cacheItem->set($value);
        if (!empty($expired)) {
            $cacheItem->expiresAfter(time() + intval($expired));
        }
        static::getCacheInstance()->save($cacheItem);
    }

    /**
     * 获取缓存内容
     * @param string $name 缓存名称
     * @return null|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public static function getCache($name)
    {
        $res = null;
        $cacheItem = static::getCacheInstance()->getItem($name);
        if ($cacheItem->isHit()) {
            $res = $cacheItem->get();
        }
        return $res;
    }

    /**
     * 移除缓存文件
     * @param string $name 缓存名称
     */
    public static function delCache($name)
    {
        static::getCacheInstance()->deleteItem($name);
    }
}