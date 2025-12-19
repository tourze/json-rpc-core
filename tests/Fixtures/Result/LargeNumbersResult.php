<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 大数结果类
 */
class LargeNumbersResult implements RpcResultInterface
{
    public function __construct(
        public readonly int $largeInt,
        public readonly float $largeFloat,
        public readonly int $negativeInt,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
