<?php

namespace Tourze\JsonRPC\Core\Exception;

/**
 * JSON-RPC运行时异常类
 * 用于处理运行时错误
 */
class JsonRpcRuntimeException extends JsonRpcException
{
    final public const CODE = -32603;

    public function __construct(string $message, array $data = [])
    {
        parent::__construct(
            self::CODE,
            $message,
            $data
        );
    }
} 