<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcMethodNotFoundException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcMethodNotFoundException extends RpcException
{
    /**
     * RpcMethodNotFoundException constructor.
     * @param string $name
     * @param string $service
     */
    public function __construct(string $name, string $service)
    {
        parent::__construct(sprintf('Method "%s" not found in service "%s"', $name, $service), self::NOT_FOUND);
    }
}
