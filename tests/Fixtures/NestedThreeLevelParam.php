<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 三层嵌套参数
 */
readonly class NestedThreeLevelParam implements RpcParamInterface
{
    public function __construct(
        public string $orderId,
        public CustomerParam $customer,
    ) {
    }
}
