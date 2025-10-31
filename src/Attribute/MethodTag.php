<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 方法标签，其实就是Swagger的接口分类。
 * 但是Swagger的接口分类是比较奇特的，一个接口可能属于多个分类.
 */
#[\Attribute(flags: \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MethodTag
{
    public function __construct(public string $name = '')
    {
    }
}
