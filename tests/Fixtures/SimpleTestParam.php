<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 简单测试参数类
 */
readonly class SimpleTestParam implements RpcParamInterface
{
    public function __construct(
        public string $name,
        public int $age = 0,
        public bool $active = true,
        public float $score = 0.0,
    ) {
    }
}
