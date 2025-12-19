<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 特殊字符结果类
 */
class SpecialCharactersResult implements RpcResultInterface
{
    public function __construct(
        public readonly string $unicodeStr,
        public readonly string $htmlStr,
        public readonly string $newlineStr,
    ) {
    }
    public static function getMockResult(): ?self
    {
        return null;
    }
}
