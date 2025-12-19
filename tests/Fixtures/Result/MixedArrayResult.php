<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 混合数组结果类
 */
class MixedArrayResult implements RpcResultInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly array $data,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
