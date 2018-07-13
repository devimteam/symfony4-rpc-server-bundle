<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcInvalidParamsException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcInvalidParamsException extends RpcException
{
    /**
     * RpcInvalidParamsException constructor.
     * @param string $data
     */
    public function __construct($data)
    {
        parent::__construct('Invalid parameters', self::INVALID_PARAMS, null, $data);
    }
}
