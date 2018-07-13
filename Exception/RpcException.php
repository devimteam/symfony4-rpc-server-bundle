<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcException extends \Exception
{
    const PARSE_ERROR = -32700;
    const INVALID_REQUEST = -32600;
    const NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL_ERROR = -32603;

    /**
     * @var mixed
     */
    private $data;

    /**
     * RpcException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param null $data
     */
    public function __construct($message, $code = 0, \Exception $previous = null, $data = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
