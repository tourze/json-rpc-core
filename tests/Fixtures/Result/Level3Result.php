<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 深层嵌套 Level 3
 */
class Level3Result implements RpcResultInterface
{
    public function __construct(
        public readonly string $value,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
