<?php

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 标记参数名
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MethodParam
{
    public function __construct(public string $description = '', public bool $optional = false)
    {
    }
}
