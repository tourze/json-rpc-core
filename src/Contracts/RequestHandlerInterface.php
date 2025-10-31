<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Contracts;

use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

interface RequestHandlerInterface
{
    public function resolveMethod(JsonRpcRequest $request): JsonRpcMethodInterface;
}
