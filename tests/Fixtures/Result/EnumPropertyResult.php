<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * Enum 属性结果类
 */
class EnumPropertyResult implements RpcResultInterface
{
    public function __construct(
        public readonly TestStatusEnum $status,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
