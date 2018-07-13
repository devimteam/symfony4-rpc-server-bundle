<?php

namespace Devim\Component\RpcServer\Interfaces;

/**
 * Interface MiddlewareInterface
 * @package Devim\Component\RpcServer\Interfaces
 */
interface MiddlewareInterface
{
    /**
     * @param $service
     * @param string $methodName
     * @param array $params
     * @return mixed
     */
    public function execute($service, string $methodName, array $params);
}
