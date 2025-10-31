<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 标记返回值
 */
#[\Attribute(flags: \Attribute::TARGET_METHOD)]
class MethodReturn
{
    public function __construct(public string $description = '')
    {
    }
}
