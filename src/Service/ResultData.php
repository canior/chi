<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 9:36
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * 通用数据返回对象
 * Class ResultData
 * @package App\Service
 * @author zxqc2018
 */
class ResultData implements \ArrayAccess
{
    /**
     * 错误码
     * @var int
     */
    private $code;

    /**
     * 错误码描述
     * @var string
     */
    private $msg;

    /**
     * 返回数据
     * @var array
     */
    private $data;

    /**
     * http请求状态码 200 正常 417 异常
     * @var int
     */
    private $statusCode;

    /**
     * ResultData constructor.
     * @param array $data
     * @param int $code
     * @param string $msg
     * @param int $statusCode
     */
    public function __construct(array $data = [], int $code = 0, string $msg = 'success', int $statusCode = 200)
    {
        $this->code = $code;
        $this->data = $data;

        //处理通用错误code对应错误码msg情况
        if ($code > 0 ) {
            $tmp_msg = $msg == 'success' ? '' : $msg;

            if (!empty($tmp_msg)) {
                $error_msg = $tmp_msg;
            } else {
                $error_msg = ErrorCode::getMessage($code);
            }
            $msg = $error_msg;

            if ($statusCode == 200) {
                $statusCode = 417;
            }
        }
        $this->statusCode = $statusCode;
        $this->msg = $msg;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return ResultData
     */
    public function setCode(int $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     * @return ResultData
     */
    public function setMsg(string $msg)
    {
        $this->msg = $msg;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return ResultData
     */
    public function setData(array $data)
    {
        $this->data = $data;
        
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * 合并data
     * @param array $data
     * @return array
     * @author zxqc2018
     */
    public function mergeData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $data;
    }
    /**
     * 返回数组形式
     * @return array
     * @author zxqc2018
     */
    public function toArray()
    {
        $res = [
            'code' => $this->getCode(),
            'msg' => $this->getMsg(),
            'data' => $this->getData(),
        ];

        return $res;
    }

    /**
     * 默认输出json字符串
     * @return string
     * @author zxqc2018
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * 返回jsonResponse给控制器
     * @param array $headers
     * @return JsonResponse
     * @author zxqc2018
     */
    public function toJsonResponse($headers = [])
    {
        return new JsonResponse($this->toArray(), $this->getStatusCode(), $headers);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}