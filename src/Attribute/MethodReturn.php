<?php

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 标记返回值
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class MethodReturn
{
    public function __construct(public string $description = '')
    {
    }
}
