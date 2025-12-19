<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 内层结果类
 */
class InnerResult implements RpcResultInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
