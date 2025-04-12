<?php

namespace Tourze\JsonRPC\Core\Exception;

/**
 * Class JsonRpcInvalidRequestException
 */
class JsonRpcInvalidRequestException extends JsonRpcException
{
    final public const CODE = -32600;

    /**
     * @param string $description Optional description of the issue
     */
    public function __construct(private $content, private readonly string $description = '')
    {
        parent::__construct(self::CODE, "Invalid request: {$this->description}");
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
