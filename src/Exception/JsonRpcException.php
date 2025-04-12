<?php

namespace Tourze\JsonRPC\Core\Exception;

/**
 * Class JsonRpcException
 */
class JsonRpcException extends \Exception implements JsonRpcExceptionInterface
{
    public function __construct(int $code, string $message = '', private array $data = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, previous: $previous);
    }

    public function getErrorCode(): int
    {
        return parent::getCode();
    }

    public function getErrorMessage(): string
    {
        return parent::getMessage();
    }

    public function getErrorData(): array
    {
        return $this->data;
    }

    public function setErrorData(array $data): void
    {
        $this->data = $data;
    }
}
