<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class AssociativeArrayParam implements RpcParamInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public array $metadata,
    ) {
    }
}
