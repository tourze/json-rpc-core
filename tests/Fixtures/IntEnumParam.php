<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class IntEnumParam implements RpcParamInterface
{
    public function __construct(
        public string $name,
        public PriorityEnum $priority,
    ) {
    }
}
