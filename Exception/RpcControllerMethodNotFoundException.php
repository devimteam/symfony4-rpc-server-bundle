<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcControllerMethodNotFoundException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcControllerMethodNotFoundException extends RpcException
{
    /**
     * RpcControllerMethodNotFoundException constructor.
     * @param string $className
     * @param string $method
     */
    public function __construct(string $className, string $method)
    {
        parent::__construct(sprintf('Controller "%s" does not found method "%s"', $className, $method));
    }
}
