<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 只读测试参数类
 */
readonly class ReadonlyTestRpcParam implements RpcParamInterface
{
    public function __construct(
        public string $name,
        public int $value,
    ) {
    }
}
