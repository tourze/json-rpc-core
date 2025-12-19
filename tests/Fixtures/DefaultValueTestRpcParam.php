<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 带默认值的测试参数类
 */
readonly class DefaultValueTestRpcParam implements RpcParamInterface
{
    public function __construct(
        public string $name = 'default',
        public int $count = 0,
    ) {
    }
}
