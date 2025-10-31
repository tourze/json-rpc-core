<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 标记参数名.
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
class MethodParam
{
    public function __construct(public string $description = '', public bool $optional = false)
    {
    }
}
