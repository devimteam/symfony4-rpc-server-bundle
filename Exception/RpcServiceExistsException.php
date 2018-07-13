<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcServiceExistsException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcServiceExistsException extends \RuntimeException
{
    /**
     * RpcServiceExistsException constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf('RPC controller "%s" exists', $name));
    }
}
