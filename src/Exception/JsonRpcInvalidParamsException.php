<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

/**
 * Class JsonRpcInvalidParamsException.
 */
class JsonRpcInvalidParamsException extends JsonRpcException
{
    final public const CODE = -32602;

    final public const DATA_VIOLATIONS_KEY = 'violations';

    /**
     * @param array<string, mixed> $violationMessageList
     */
    public function __construct(array $violationMessageList)
    {
        parent::__construct(
            self::CODE,
            'Invalid params',
            [
                self::DATA_VIOLATIONS_KEY => $violationMessageList,
            ]
        );
    }
}
