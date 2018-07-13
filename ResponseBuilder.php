<?php

namespace Devim\Component\RpcServer;
use Devim\Component\RpcServer\Exception\RpcException;

/**
 * Class ResponseBuilder
 * @package Devim\Component\RpcServer
 */
class ResponseBuilder
{
    /**
     * @param $id|null
     * @param $data
     * @return array
     */
    public static function build($id, $data) : array
    {
        $response = ['jsonrpc' => RpcServer::JSON_RPC_VERSION];

        if ($data instanceof \Throwable) {
            $response['error'] = self::buildError($data);
        } else {
            $response['result'] = $data;
        }

        $response['id'] = $id;

        return $response;
    }

    /**
     * @param \Throwable $exception
     * @return array
     */
    private static function buildError(\Throwable $exception) : array
    {
        $response = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ];

        if ($exception instanceof RpcException && null !== $exception->getData()) {
            $response['data'] = $exception->getData();
        }

        return $response;
    }
}
