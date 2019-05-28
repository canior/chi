<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/28
 * Time: 17:24
 */

namespace App\Exception;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * api自定义异常
 * Class ApiHttpException
 * @package App\Exception
 * @author zxqc2018
 */
class ApiHttpException extends HttpException
{
    private $data = [];

    /**
     * ApiHttpException constructor.
     * @param int $code
     * @param array $data
     * @param string|null $message
     * @param int $statusCode
     */
    public function __construct(int $code, $data = [], string $message = '', int $statusCode = Response::HTTP_EXPECTATION_FAILED)
    {
        $this->data = $data;
        parent::__construct($statusCode, $message, null, [], $code);
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
     * @return ApiHttpException
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
}