<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 没有 MethodParam 属性的参数类（文档提取测试专用）
 */
readonly class DocExtractorNoMethodParamParam implements RpcParamInterface
{
    public function __construct(
        public string $id,
        public int $count = 0,
    ) {
    }
}
