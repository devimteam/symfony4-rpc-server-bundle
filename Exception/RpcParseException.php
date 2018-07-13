<?php

namespace Devim\Component\RpcServer\Exception;

/**
 * Class RpcParseException
 * @package Devim\Component\RpcServer\Exception
 */
class RpcParseException extends RpcException
{
    /**
     * RpcParseException constructor.
     */
    public function __construct()
    {
        parent::__construct('Parse error', self::PARSE_ERROR);
    }
}
