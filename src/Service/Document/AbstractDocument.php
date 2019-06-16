<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/15
 * Time: 22:56
 */

namespace App\Service\Document;

use App\Service\ErrorCode;
use App\Service\ResultData;
use App\Service\Tool\HttpClient;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 通用http文档请求抽象类
 * Class AbstractDocument
 * @package App\Service\Document
 * @author zxqc2018
 */
abstract class AbstractDocument
{
    //请求配置数组
    protected $config = [];

    //http请求服务类
    protected $http_client;

    /**
     * AbstractDocument constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $this->http_client = new HttpClient($this->config);
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->http_client;
    }

    /**
     * @param HttpClient $http_client
     */
    public function setHttpClient(HttpClient $http_client)
    {
        $this->http_client = $http_client;
    }

    /**
     * 通过http服务查询远程文档
     * @param array|callable $query_arr get参数数组或匿名方法
     * @param string $method_str 请求方式 GET POST PUT
     * @param null $post_fields post|put参数数组
     * @param string $doc_type_str 返回类型 XML JSON OTHER
     * @param array $extra_data 额外参数数组 如['cookie' => ['a' => 11], 'retry' => false, 'http_header' => ['aa','bb']]
     * @return mixed
     * @author zxqc2018
     */
    public function select($query_arr, $method_str = 'GET', $post_fields = null, $doc_type_str = 'JSON', $extra_data = [])
    {
        $queryStr = '';

        if (is_callable($query_arr)) {
            $queryStr = $query_arr();
        } else if (!empty($query_arr)) {
            $queryStr = http_build_query($query_arr);
        }

        $http_client = $this->getHttpClient();

        $cookie_fields = $extra_data['cookie'] ?? null;
        $retry = $extra_data['retry'] ?? false;
        $http_header = $extra_data['http_header'] ?? [];

        $res = $http_client->query($queryStr, $method_str, $post_fields, $cookie_fields, $http_header);

        if (isset($res['err']) && $retry) {

            sleep(1);
            $res = $http_client->query($queryStr, $method_str, $post_fields, $cookie_fields, $http_header);

            if (isset($res['err'])) {

                sleep(3);
                $res = $http_client->query($queryStr, $method_str, $post_fields, $cookie_fields, $http_header);
            }
        }

        if (!isset($res['err'])) {

            $doc_type_str = strtoupper($doc_type_str);
            switch ($doc_type_str) {
                case 'JSON':
                    try {
                        $doc = json_decode($res['txt'], true);
                    } catch (\Exception $e) {
                        $doc = [];
                    }
                    $res['doc'] = $doc;
                    break;
                case 'XML':
                    try {
                        $xml = simplexml_load_string($res['txt']);
                        $doc = json_decode(json_encode($xml), true);
                    } catch (\Exception $e) {
                        $doc = [];
                    }
                    $res['doc'] = $doc;
                    break;
                default:
                    $res['doc'] = ['txt' => $res['txt']];
            }
        }

        return $res;
    }

    /**
     * 通过http服务查询远程文档附带path
     * @param string $path url路径
     * @param array|callable $query_arr get参数数组或匿名方法
     * @param string $method_str 请求方式 GET POST PUT
     * @param null $post_fields post|put参数数组
     * @param string $doc_type_str 返回类型 XML JSON OTHER
     * @param array $extra_data 额外参数数组 如['cookie' => ['a' => 11], 'retry' => false, 'http_header' => ['aa','bb']]
     * @return array
     * @author zxqc2018
     */
    protected function selectWithPath($path, $query_arr, $method_str = 'GET', $post_fields = null, $doc_type_str = 'JSON', $extra_data = [])
    {
        $res = [];
        $this->http_client->setPath($path);

        $res_data = $this->select($query_arr, $method_str, $post_fields, $doc_type_str, $extra_data);

        if(!empty($res_data['err'])) {
            $error_msg = $res_data['err'] == 28 ? '连接超时' : $res_data['error'];
        }else{
            if(empty($res_data['doc'])) {
                if($res_data['inf']['http_code'] > 400) {
                    $error_msg = "http {$res_data['inf']['http_code']} error";
                }
            }else{
                $res = $res_data['doc'];
            }
        }

        //假如有错误信息
        if (!empty($error_msg)) {
            CommonUtil::resultData([], ErrorCode::ERROR_COMMON_NETWORK_ERROR, $error_msg)->throwErrorException();
        }
        return $res;
    }

    /**
     * 默认处理返回值
     * @param mixed $responseData 返回数据
     * @return \App\Service\ResultData
     * @author zxqc2018
     */
    protected function responseDataProcess($responseData)
    {
        return CommonUtil::resultData($responseData);
    }

    /**
     * 取得响应结果
     * @param string $path url路径
     * @param array|callable $query_arr get参数数组或匿名方法
     * @param string $method_str 请求方式 GET POST PUT
     * @param null $post_fields post|put参数数组
     * @param string $doc_type_str 返回类型 XML JSON OTHER
     * @param array $extra_data 额外参数数组 如['cookie' => ['a' => 11], 'retry' => false, 'http_header' => ['aa','bb']]
     * @return ResultData
     * @author zxqc2018
     */
    protected function getResponse($path, $query_arr, $method_str = 'GET', $post_fields = null, $doc_type_str = 'JSON', $extra_data = [])
    {
        try {
            $res = $this->responseDataProcess($this->selectWithPath($path, $query_arr, $method_str, $post_fields, $doc_type_str, $extra_data));
        } catch (HttpException $e) {
            $res = CommonUtil::resultData([], $e->getCode(), $e->getMessage());
        } catch (\Throwable $e) {
            $res = CommonUtil::resultData([], ErrorCode::ERROR_COMMON_UNKNOWN_ERROR, $e->getMessage());
        }

        return $res;
    }
}
