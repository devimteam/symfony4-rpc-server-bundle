<?php

namespace Devim\Component\RpcServer\Interfaces;

/**
 * Interface MiddlewareProviderInteface
 * @package Devim\Component\RpcServer\Interfaces
 */
interface MiddlewareProviderInteface
{
    /**
     * @return MiddlewareInterface[]
     */
    public function get(): array;
}
