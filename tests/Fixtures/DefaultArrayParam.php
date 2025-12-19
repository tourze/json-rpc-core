<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class DefaultArrayParam implements RpcParamInterface
{
    /**
     * @param string[] $items
     */
    public function __construct(
        public string $name,
        public array $items = [],
    ) {
    }
}
