<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 全 null 属性结果类
 */
class AllNullPropertiesResult implements RpcResultInterface
{
    public function __construct(
        public readonly ?string $a,
        public readonly ?int $b,
        public readonly ?array $c,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
