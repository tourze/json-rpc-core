<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

/**
 * JsonRPC 无效请求异常.
 */
class JsonRpcInvalidRequestException extends JsonRpcException
{
    final public const CODE = -32600;

    /**
     * @param string $description Optional description of the issue
     */
    public function __construct(private mixed $content, private readonly string $description = '')
    {
        parent::__construct(self::CODE, "Invalid request: {$this->description}");
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
