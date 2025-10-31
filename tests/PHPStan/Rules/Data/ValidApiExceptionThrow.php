<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\PHPStan\Rules\Data;

use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class ValidApiExceptionThrow implements JsonRpcMethodInterface
{
    public function __invoke(JsonRpcRequest $request): mixed
    {
        if (false) {
            throw new ApiException('Valid throw');
        }

        return [];
    }

    public function execute(): array
    {
        return [];
    }
}
