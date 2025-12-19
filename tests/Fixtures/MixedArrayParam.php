<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class MixedArrayParam implements RpcParamInterface
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        public string $name,
        public array $items,
    ) {
    }
}
