<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 包含数组类型的参数类（文档提取测试专用）
 */
readonly class DocExtractorArrayParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '标签列表')]
        /** @var string[] */
        public array $tags = [],

        #[MethodParam(description: '数量列表')]
        /** @var int[] */
        public array $counts = [],
    ) {
    }
}
