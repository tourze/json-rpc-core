<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 可空字段测试参数类
 */
readonly class NullableTestParam implements RpcParamInterface
{
    public function __construct(
        public string $required,
        public ?string $optional = null,
    ) {
    }
}
