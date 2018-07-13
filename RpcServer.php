<?php

namespace Devim\Component\RpcServer;

use Devim\Component\RpcServer\Exception\RpcInternalErrorException;
use Devim\Component\RpcServer\Interfaces\SerializableServiceInterface;
use JMS\Serializer\SerializerInterface;

use Devim\Component\RpcServer\Interfaces\MiddlewareInterface;
use Devim\Component\RpcServer\Interfaces\RpcServerInterface;

use Devim\Component\RpcServer\Interfaces\MiddlewareProviderInteface;
use Devim\Component\RpcServer\Interfaces\RpcServiceProviderInterface;

use Devim\Component\RpcServer\Exception\RpcParseException;
use Devim\Component\RpcServer\Exception\RpcServiceExistsException;
use Devim\Component\RpcServer\Exception\RpcInvalidParamsException;
use Devim\Component\RpcServer\Exception\RpcInvalidRequestException;
use Devim\Component\RpcServer\Exception\RpcMethodNotFoundException;
use Devim\Component\RpcServer\Exception\RpcServiceNotFoundException;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RpcServer
 * @package Devim\Component\RpcServer
 */
class RpcServer implements RpcServerInterface
{
    const JSON_RPC_VERSION = '2.0';

    /**
     * @var SerializerInterface|null
     */
    private $serializer;

    /**
     * @var MiddlewareInterface[]
     */
    private $middleware = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * RpcServer constructor.
     * @param MiddlewareProviderInteface|null $middlewareProvider
     * @param RpcServiceProviderInterface|null $rpcServiceProvider
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        ?MiddlewareProviderInteface $middlewareProvider = null,
        ?RpcServiceProviderInterface $rpcServiceProvider = null,
        ?SerializerInterface $serializer
    ) {
        if ($middlewareProvider !== null) {
            foreach ($middlewareProvider->get() as $middleware) {
                $this->addMiddleware($middleware);
            }
        }

        if ($rpcServiceProvider !== null) {
            foreach ($rpcServiceProvider->get() as $service) {
                $this->addService($service);
            }
        }

        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function setSerializer(SerializerInterface $serializer): RpcServerInterface
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface $middleware): RpcServerInterface
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addService($service): RpcServerInterface
    {
        $classShortName = (new \ReflectionClass($service))->getShortName();
        $name = lcfirst(substr($classShortName, 0, strlen($classShortName) - 10));

        if (isset($this->services[$name])) {
            throw new RpcServiceExistsException($name);
        }

        $this->services[$name] = $service;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function run(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $responses = [];
        $requests = $this->isBatchRequest($payload) ? $payload : [$payload];

        foreach ($requests as $request) {
            list($response, $serializable, $serializationCtx) = $this->doRun($request);
            if ($serializable === false) {
                $responses[] = $response;
                continue ;
            }

            if ($this->serializer === null) {
                throw new RpcInternalErrorException('serializer is not set');
            }

            $responses[] = json_decode(
                $this->serializer->serialize($response, 'json', $serializationCtx),
                true
            );
        }

        if (count($responses) == 1) {
            $responses = reset($responses);
        }

        return JsonResponse::create($responses);
    }

    /**
     * @param mixed $payload
     * @return mixed
     * @throws RpcServiceNotFoundException
     * @throws RpcParseException
     * @throws RpcMethodNotFoundException
     * @throws RpcInvalidParamsException
     * @throws RpcInvalidRequestException
     */
    private function doRun($payload)
    {
        try {
            $this->validatePayload($payload);
        } catch (\Throwable $e) {
            return [$this->handleExceptions($payload, $e), false, null];
        }

        try {
            return $this->parseRequest($payload);
        } catch (\Throwable $e) {
            return [$this->handleExceptions($payload, $e), false, null];
        }
    }

    /**
     * @param mixed $payload
     * @throws RpcInvalidRequestException
     * @throws RpcParseException
     */
    private function validatePayload(&$payload)
    {
        if (null === $payload) {
            throw new RpcParseException();
        }

        if (!isset($payload['jsonrpc']) ||
            !isset($payload['method']) ||
            !is_string($payload['method']) ||
            $payload['jsonrpc'] !== RpcServer::JSON_RPC_VERSION ||
            (isset($payload['params']) && !is_array($payload['params']))
        ) {
            throw new RpcInvalidRequestException();
        }
    }

    /**
     * @param mixed $payload
     * @return bool
     */
    private function isBatchRequest($payload) : bool
    {
        return is_array($payload) && array_keys($payload) === range(0, count($payload) - 1);
    }

    /**
     * @param array $payload
     * @return mixed
     * @throws RpcParseException
     * @throws RpcServiceNotFoundException
     * @throws RpcInvalidParamsException
     * @throws RpcMethodNotFoundException
     * @throws RpcInvalidRequestException
     */
    private function parseRequest(array $payload)
    {
        list($serviceName, $methodName) = $this->extractServiceAndMethodNames($payload['method']);

        $service = $this->getService($serviceName);
        if (!method_exists($service, $methodName)) {
            throw new RpcMethodNotFoundException($methodName, $serviceName);
        }

        $params = $payload['params'] ?? [];
        $params = $this->extractParametersValue($service, $methodName, $params);

        foreach ($this->middleware as $middleware) {
            $middleware->execute($service, $methodName, $params);
        }

        $result = $this->invokeMethod($service, $methodName, $params);

        return [
            ResponseBuilder::build($this->extractRequestId($payload), $result),
            $service instanceof SerializableServiceInterface,
            $service instanceof SerializableServiceInterface ? $service->getSerializationContext() : null
        ];
    }

    /**
     * @param string $method
     * @return array
     */
    private function extractServiceAndMethodNames(string $method) : array
    {
        $methodInfo = explode('.', $method, 2);
        $serviceName = isset($methodInfo[1]) ? $methodInfo[0] : false;
        $methodName = $serviceName !== false ? $methodInfo[1] : $method;

        return [$serviceName, $methodName];
    }

    /**
     * @param string $name
     * @return mixed
     * @throws RpcServiceNotFoundException
     */
    private function getService(string $name)
    {
        if (!array_key_exists($name, $this->services)) {
            throw new RpcServiceNotFoundException($name);
        }

        return $this->services[$name];
    }

    /**
     * @param $service
     * @param string $method
     * @param array $params
     * @return array
     * @throws RpcInvalidParamsException
     */
    private function extractParametersValue($service, string $method, array $params) : array
    {
        $results = [];

        $reflectionMethod = new \ReflectionMethod($service, $method);

        if (array_keys($params) === range(0, count($params) - 1)) {
            $results = $params;
        } else {
            foreach ($reflectionMethod->getParameters() as $parameter) {
                $paramName = $parameter->getName();

                if (array_key_exists($paramName, $params)) {
                    $results[] = $params[$paramName];
                } else {
                    if ($parameter->isDefaultValueAvailable()) {
                        $results[] = $parameter->getDefaultValue();
                    }
                }
            }
        }

        if (count($results) !== $reflectionMethod->getNumberOfParameters()) {
            throw new RpcInvalidParamsException('Invalid number of required parameters');
        }

        return $results;
    }

    /**
     * @param $service
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws RpcInvalidParamsException
     */
    private function invokeMethod($service, string $method, array $params)
    {
        $paramsValue = $this->extractParametersValue($service, $method, $params);

        return (new \ReflectionMethod($service, $method))->invokeArgs($service, $paramsValue);
    }

    /**
     * @param $payload
     * @param \Throwable $exception
     * @return array
     */
    private function handleExceptions($payload, \Throwable $exception) : array
    {
        return ResponseBuilder::build($this->extractRequestId($payload), $exception);
    }

    /**
     * @param $payload
     * @return null|int
     */
    private function extractRequestId($payload)
    {
        return $payload['id'] ?? null;
    }
}
