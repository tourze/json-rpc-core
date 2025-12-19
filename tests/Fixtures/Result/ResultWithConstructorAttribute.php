<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Attribute\ResultProperty;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 带构造函数参数注解的测试类
 */
class ResultWithConstructorAttribute implements RpcResultInterface
{
    public function __construct(
        #[ResultProperty(description: 'ID字段')]
        public readonly int $id,
        #[ResultProperty(description: '名称字段', nullable: true)]
        public readonly ?string $name,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
