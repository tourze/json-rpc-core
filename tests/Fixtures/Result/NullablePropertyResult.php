<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Attribute\ResultProperty;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 带可空属性的测试用 Result 类
 */
class NullablePropertyResult implements RpcResultInterface
{
    public function __construct(
        public readonly int $id,
        #[ResultProperty(nullable: true)]
        public readonly ?string $email,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
