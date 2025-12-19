<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 外层结果类（包含嵌套）
 */
class OuterResult implements RpcResultInterface
{
    public function __construct(
        public readonly string $title,
        public readonly RpcResultInterface $nested,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
