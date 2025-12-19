<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 可选嵌套参数
 */
readonly class NestedOptionalParam implements RpcParamInterface
{
    public function __construct(
        public string $name,
        public ?AddressParam $address = null,
    ) {
    }
}
