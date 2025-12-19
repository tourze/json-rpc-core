<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 空数组结果类
 */
class EmptyArrayResult implements RpcResultInterface
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        public readonly array $items,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
