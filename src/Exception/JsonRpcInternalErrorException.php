<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

use Tourze\BacktraceHelper\ExceptionPrinter;

/**
 * JsonRpc 内部错误异常类。
 */
class JsonRpcInternalErrorException extends JsonRpcException
{
    final public const CODE = -32603;

    final public const DATA_PREVIOUS_KEY = 'previous';

    public function __construct(?\Throwable $previousException = null)
    {
        $data = [];

        if (null !== $previousException) {
            if ('prod' === ($_ENV['APP_ENV'] ?? '')) {
                $prevMessage = $previousException->getMessage();
            } else {
                $prevMessage = ExceptionPrinter::exception($previousException);
            }
            if (filter_var($_ENV['JSON_RPC_RESPONSE_FULL_ERROR'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $prevMessage = ExceptionPrinter::exception($previousException);
            }

            $data = [self::DATA_PREVIOUS_KEY => $prevMessage];
        }

        $message = $_ENV['JSON_RPC_RESPONSE_EXCEPTION_MESSAGE'] ?? 'Internal error';
        assert(is_string($message));

        parent::__construct(
            self::CODE,
            $message,
            $data
        );
    }
}
