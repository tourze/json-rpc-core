<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * DateTime 属性结果类
 */
class DateTimePropertyResult implements RpcResultInterface
{
    public function __construct(
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
