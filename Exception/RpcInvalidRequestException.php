<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcInvalidRequestException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcInvalidRequestException extends RpcException
{
    /**
     * RpcInvalidRequestException constructor.
     */
    public function __construct()
    {
        parent::__construct('Invalid Request', self::INVALID_REQUEST);
    }
}
