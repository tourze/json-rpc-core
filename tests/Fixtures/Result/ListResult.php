<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 列表结果类
 */
class ListResult implements RpcResultInterface
{
    /**
     * @param RpcResultInterface[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
