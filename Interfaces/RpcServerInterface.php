<?php

namespace Devim\Component\RpcServer\Interfaces;

use JMS\Serializer\SerializerInterface;

use Devim\Component\RpcServer\Exception\RpcParseException;
use Devim\Component\RpcServer\Exception\RpcInternalErrorException;
use Devim\Component\RpcServer\Exception\RpcServiceExistsException;
use Devim\Component\RpcServer\Exception\RpcInvalidParamsException;
use Devim\Component\RpcServer\Exception\RpcMethodNotFoundException;
use Devim\Component\RpcServer\Exception\RpcInvalidRequestException;
use Devim\Component\RpcServer\Exception\RpcServiceNotFoundException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Interface RpcServerInterface
 * @package Devim\Component\RpcServer
 */
interface RpcServerInterface
{
    /**
     * @param MiddlewareInterface $middleware
     * @return RpcServerInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): RpcServerInterface;

    /**
     * @param object $service
     * @return RpcServerInterface
     * @throws RpcServiceExistsException
     */
    public function addService($service): RpcServerInterface;

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RpcServiceNotFoundException
     * @throws RpcParseException
     * @throws RpcMethodNotFoundException
     * @throws RpcInvalidRequestException
     * @throws RpcInvalidParamsException
     * @throws RpcInternalErrorException
     */
    public function run(Request $request) : JsonResponse;

    /**
     * @param SerializerInterface $serializer
     * @return RpcServerInterface
     */
    public function setSerializer(SerializerInterface $serializer): RpcServerInterface;
}
