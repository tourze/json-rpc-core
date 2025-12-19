<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class OptionalArrayParam implements RpcParamInterface
{
    /**
     * @param string[]|null $items
     */
    public function __construct(
        public string $name,
        public ?array $items = null,
    ) {
    }
}
