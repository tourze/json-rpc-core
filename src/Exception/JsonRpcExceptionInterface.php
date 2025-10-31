<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

/**
 * JsonRpc 异常接口。
 */
interface JsonRpcExceptionInterface
{
    /**
     * @return int JsonRpc error code
     */
    public function getErrorCode(): int;

    /**
     * @return string JsonRpc error message
     */
    public function getErrorMessage(): string;

    /**
     * @return array<string, mixed> Optional error data
     */
    public function getErrorData(): array;
}
