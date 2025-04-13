<?php

namespace Tourze\JsonRPC\Core\Exception;

class AccessDeniedException extends JsonRpcException
{
    final public const DATA_PREVIOUS_KEY = 'previous';

    final public const ERROR_CODE = -996;

    final public const ERROR_MESSAGE = '当前用户未获得访问授权!';

    public function __construct(?\Exception $previousException = null)
    {
        parent::__construct(
            self::ERROR_CODE,
            self::ERROR_MESSAGE,
            $previousException ? [self::DATA_PREVIOUS_KEY => $previousException->getMessage()] : []
        );
    }
}
