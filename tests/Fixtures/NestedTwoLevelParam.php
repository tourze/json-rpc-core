<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 二层嵌套参数
 */
readonly class NestedTwoLevelParam implements RpcParamInterface
{
    public function __construct(
        public string $name,
        public AddressParam $address,
    ) {
    }
}
