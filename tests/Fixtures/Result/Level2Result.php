<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 深层嵌套 Level 2
 */
class Level2Result implements RpcResultInterface
{
    public function __construct(
        public readonly RpcResultInterface $level3,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
