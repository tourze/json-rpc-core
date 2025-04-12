<?php

namespace Tourze\JsonRPC\Core\Exception;

use Tourze\BacktraceHelper\ExceptionPrinter;

/**
 * Class JsonRpcInternalErrorException
 */
class JsonRpcInternalErrorException extends JsonRpcException
{
    final public const CODE = -32603;

    final public const DATA_PREVIOUS_KEY = 'previous';

    public function __construct(?\Throwable $previousException = null)
    {
        if ('prod' === $_ENV['APP_ENV']) {
            $prevMessage = $previousException->getMessage();
        } else {
            $prevMessage = ExceptionPrinter::exception($previousException);
        }
        if ($_ENV['JSON_RPC_RESPONSE_FULL_ERROR'] ?? false) {
            $prevMessage = ExceptionPrinter::exception($previousException);
        }

        parent::__construct(
            self::CODE,
            $_ENV['JSON_RPC_RESPONSE_EXCEPTION_MASSAGE'] ?? 'Internal error',
            $previousException ? [self::DATA_PREVIOUS_KEY => $prevMessage] : []
        );
    }
}
