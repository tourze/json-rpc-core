<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

/**
 * JSON-RPC参数异常类
 * 用于处理无效参数相关的错误.
 */
class JsonRpcArgumentException extends JsonRpcException
{
    final public const CODE = -32602;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $message, array $data = [])
    {
        parent::__construct(
            self::CODE,
            $message,
            $data
        );
    }
}
