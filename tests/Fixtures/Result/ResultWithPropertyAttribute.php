<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Attribute\ResultProperty;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 带属性注解的测试类
 */
class ResultWithPropertyAttribute implements RpcResultInterface
{
    #[ResultProperty(description: '测试属性')]
    public int $id = 1;
    public static function getMockResult(): ?self
    {
        return null;
    }
}
