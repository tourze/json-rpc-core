<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\PHPStan\Rules\Data;

use Tourze\JsonRPC\Core\Exception\ApiException;

class InvalidApiExceptionThrow
{
    public function someMethod(): void
    {
        if (false) {
            throw new ApiException('Invalid throw - class does not implement JsonRpcMethodInterface');
        }
    }
}
