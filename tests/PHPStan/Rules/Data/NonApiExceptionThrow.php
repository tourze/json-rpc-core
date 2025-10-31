<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\PHPStan\Rules\Data;

class NonApiExceptionThrow
{
    public function someMethod(): void
    {
        if (false) {
            throw new \RuntimeException('This should not trigger the rule');
        }
    }
}
