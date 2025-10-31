<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

/**
 * JsonRpc 异常基类.
 */
abstract class JsonRpcException extends \Exception implements JsonRpcExceptionInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(int $code, string $message = '', private array $data = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, previous: $previous);
    }

    public function getErrorCode(): int
    {
        $code = parent::getCode();
        assert(is_int($code));

        return $code;
    }

    public function getErrorMessage(): string
    {
        return parent::getMessage();
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrorData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setErrorData(array $data): void
    {
        $this->data = $data;
    }
}
