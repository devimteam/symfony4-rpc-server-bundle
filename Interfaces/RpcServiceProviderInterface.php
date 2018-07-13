<?php

namespace Devim\Component\RpcServer\Interfaces;

/**
 * Interface RpcServiceProviderInterface
 * @package Devim\Component\RpcServer\Interfaces
 */
interface RpcServiceProviderInterface
{
    /**
     * @return object[]
     */
    public function get(): array;
}
