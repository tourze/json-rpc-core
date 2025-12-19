<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 用于测试循环引用的类
 */
class CircularReferenceResult implements RpcResultInterface
{
    public ?self $self = null;
    public static function getMockResult(): ?self
    {
        return null;
    }
}
