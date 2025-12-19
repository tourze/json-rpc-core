<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 测试用参数类
 */
class TestRpcParam implements RpcParamInterface
{
    public function __construct(
        public string $value,
    ) {
    }
}
