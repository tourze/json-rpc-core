<?php

namespace Tourze\JsonRPC\Core\Exception;

/**
 * Interface JsonRpcExceptionInterface
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
     * @return array Optional error data
     */
    public function getErrorData(): array;
}
