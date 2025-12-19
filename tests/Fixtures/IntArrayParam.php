<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class IntArrayParam implements RpcParamInterface
{
    /**
     * @param int[] $values
     */
    public function __construct(
        public string $name,
        public array $values,
    ) {
    }
}
