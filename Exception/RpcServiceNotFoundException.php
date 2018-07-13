<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcServiceNotFoundException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcServiceNotFoundException extends RpcException
{
    /**
     * RpcServiceNotFoundException constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Service "%s" not found', $name), self::NOT_FOUND);
    }
}
