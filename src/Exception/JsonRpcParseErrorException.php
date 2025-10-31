<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

/**
 * JSON-RPC 解析错误异常.
 */
class JsonRpcParseErrorException extends JsonRpcException
{
    final public const CODE = -32700;

    public function __construct(private mixed $content, private mixed $parseErrorCode = null, private readonly string $parseErrorMessage = '')
    {
        parent::__construct(self::CODE, 'Parse error');
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getParseErrorCode(): mixed
    {
        return $this->parseErrorCode;
    }

    public function getParseErrorMessage(): ?string
    {
        return $this->parseErrorMessage;
    }
}
