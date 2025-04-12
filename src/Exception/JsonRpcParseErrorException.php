<?php

namespace Tourze\JsonRPC\Core\Exception;

/**
 * Class JsonRpcParseErrorException
 */
class JsonRpcParseErrorException extends JsonRpcException
{
    final public const CODE = -32700;

    public function __construct(private $content, private $parseErrorCode = null, private readonly string $parseErrorMessage = '')
    {
        parent::__construct(self::CODE, 'Parse error');
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getParseErrorCode()
    {
        return $this->parseErrorCode;
    }

    public function getParseErrorMessage(): ?string
    {
        return $this->parseErrorMessage;
    }
}
