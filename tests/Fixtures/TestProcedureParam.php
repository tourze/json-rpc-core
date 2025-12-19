<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 测试用参数类
 */
readonly class TestProcedureParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '名称')]
        public string $name,

        #[MethodParam(description: '年龄')]
        public int $age = 0,
    ) {
    }
}
