<?php

namespace Devim\Component\RpcServer\Interfaces;
use JMS\Serializer\SerializationContext;

/**
 * Interface SerializableServiceInterface
 * @package Devim\Component\RpcServer\Interfaces
 */
interface SerializableServiceInterface
{
    /**
     * @return SerializationContext|null
     */
    public function getSerializationContext(): ?SerializationContext;
}
