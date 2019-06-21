<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 9:36
 */

namespace App\Service;

use App\Exception\ApiHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * 通用数据返回对象
 * Class ResultData
 * @package App\Service
 * @author zxqc2018
 */
class ResultData implements \ArrayAccess
{

    const SUCCESS_MSG = 'success';

    const HTTP_OK_STATUS_CODE = Response::HTTP_OK;
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
     * @var string 对象初始化字符串
     */
    private $originMsg;

    /**
     * @var int 对象初始化statusCode
     */
    private $originStatusCode;
    /**
     * ResultData constructor.
     * @param array $data
     * @param int $code
     * @param string $msg
     * @param int $statusCode
     */
    public function __construct(array $data = [], int $code = 0, string $msg = self::SUCCESS_MSG, int $statusCode = self::HTTP_OK_STATUS_CODE)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->msg = $msg;
        $this->setOriginMsg($msg);
        $this->setOriginStatusCode($statusCode);
        $this->setCode($code);
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
        //处理通用错误code对应错误码msg情况
        if ($code > 0 ) {
            if ($code < 10000) {
                $error_msg = Response::$statusTexts[$code] ?? Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR];
                $this->setStatusCode($code);
            } else {
                $error_msg = ErrorCode::getMessage($code);
                $this->setStatusCode(Response::HTTP_EXPECTATION_FAILED);
            }
            $this->setMsg($error_msg);
        }
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
        if (empty($this->originMsg) || $this->originMsg == self::SUCCESS_MSG) {
            $this->msg = $msg;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginMsg(): string
    {
        return $this->originMsg;
    }

    /**
     * @param string $originMsg
     */
    public function setOriginMsg(string $originMsg): void
    {
        $this->originMsg = $originMsg;
    }

    /**
     * @param string $msg
     * @return $this
     * @author zxqc2018
     */
    public function forceSetMsg(string  $msg) :self
    {
        $this->msg = $msg;
        return $this;
    }
    /**
     * @return int
     */
    public function getOriginStatusCode(): int
    {
        return $this->originStatusCode;
    }

    /**
     * @param int $originStatusCode
     */
    public function setOriginStatusCode(int $originStatusCode): void
    {
        $this->originStatusCode = $originStatusCode;
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
     * @return ResultData
     */
    public function setStatusCode(int $statusCode)
    {
        if (empty($this->statusCode) || $this->originStatusCode == self::HTTP_OK_STATUS_CODE) {
            $this->statusCode = $statusCode;
        }

        return $this;
    }

    /**
     * 合并data
     * @param array $data
     * @author zxqc2018
     * @return ResultData
     */
    public function mergeData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * 是否成功返回
     * @return bool
     * @author zxqc2018
     */
    public function isSuccess()
    {
        return empty($this->getCode());
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
     * @param null $data 数据
     * @param array $headers
     * @return JsonResponse
     * @author zxqc2018
     */
    public function toJsonResponse($data = null, $headers = [])
    {
        if (!is_null($data)) {
            $this->setData($data);
        }

        return new JsonResponse($this->toArray(), $this->getStatusCode(), $headers);
    }


    /**
     * 对象转化为异常抛出
     * @param null $code
     * @param null $data
     * @param null $msg
     * @param array $excludeCodeArr 排除抛出异常的code数组
     * @return $this
     * @author zxqc2018
     */
    public function throwErrorException($code = null, $data = null, $msg = null, $excludeCodeArr = [])
    {
        if (!is_null($code)) {
            $this->setCode($code);
        }
        if (!is_null($data)) {
            $this->setData($data);
        }

        if (!is_null($msg)) {
            $this->setOriginMsg('');
            $this->setMsg($msg);
        }

        //处理需要抛出异常的情况
        if ($this->getCode() > 0 && !in_array($this->getCode(), $excludeCodeArr)) {
            $apiHttpException = new ApiHttpException($this->getCode(), $this->getData(), $this->getMsg());
            throw $apiHttpException;
        }
        return $this;
    }

    /**
     * 普通Response
     * @param null $content
     * @param int $statusCode
     * @param array $headers
     * @return Response
     * @author zxqc2018
     */
    public function toResponse($content = null, $statusCode = Response::HTTP_OK, $headers = [])
    {
        if (!is_null($content)) {
            $this->setOriginMsg('');
            $this->setMsg($content);
        }

        return new Response($this->getMsg(), $statusCode, $headers);
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