<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 深层嵌套 Level 1
 */
class Level1Result implements RpcResultInterface
{
    public function __construct(
        public readonly RpcResultInterface $level2,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
