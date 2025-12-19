<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Attribute\ResultProperty;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 测试用自定义 Result 类
 */
class CustomResult implements RpcResultInterface
{
    public function __construct(
        #[ResultProperty(description: 'ID')]
        public readonly int $id,
        #[ResultProperty(description: '名称')]
        public readonly string $name,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
