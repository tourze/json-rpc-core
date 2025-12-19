<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 空响应结果
 *
 * 用于不需要返回具体数据的场景，序列化为 {}
 */
readonly class EmptyResult implements RpcResultInterface
{
    // 空结果，序列化为 {}

    public static function getMockResult(): ?self
    {
        return new self();
    }
}
