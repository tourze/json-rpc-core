<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 带数组属性的测试用 Result 类
 */
class ArrayPropertyResult implements RpcResultInterface
{
    /**
     * @param string[] $items
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
