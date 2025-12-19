<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 标记 Result 类属性的文档元数据
 *
 * 与 MethodParam 对称设计，用于：
 * - 文档生成
 * - IDE 提示
 * - API 文档导出
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class ResultProperty
{
    public function __construct(
        public string $description = '',
        public bool $nullable = false,
    ) {
    }
}
